<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Clients;

use Fin\Narekaltro\Domain\Clients\ClientHistoryEntry;
use Fin\Narekaltro\Domain\Clients\ClientHistoryRepository;
use Fin\Narekaltro\Domain\Clients\ClientHistoryService;
use Fin\Narekaltro\Domain\Shared\PageRequest;
use Fin\Narekaltro\Domain\Shared\PageResult;
use Fin\Narekaltro\Infrastructure\Database\Connection;
use mysqli;
use mysqli_stmt;

final class MysqliClientHistoryRepository implements ClientHistoryRepository
{
	public function __construct(private Connection $connection)
	{
	}

	#[\Override]
	public function forClient(int $clientId, string $accountId): array
	{
		$appointments = $this->appointmentRows($clientId, $accountId);

		return $this->historyFromAppointments($appointments, $accountId);
	}

	#[\Override]
	public function pageForClient(int $clientId, string $accountId, PageRequest $page): PageResult
	{
		$total = $this->appointmentCount($clientId, $accountId);
		$page = $page->withinTotal($total);
		$appointments = $this->appointmentRows($clientId, $accountId, $page);

		return new PageResult(
			$this->historyFromAppointments($appointments, $accountId),
			$total,
			$page
		);
	}

	private function historyFromAppointments(array $appointments, string $accountId): array
	{
		$services = $this->servicesForAccount($accountId);
		$appointmentIds = array_values(array_unique(array_map(
			static fn (array $appointment): int => (int) $appointment['appointment_id'],
			$appointments
		)));
		$costs = $this->costsForAppointments($appointmentIds, $accountId);
		$history = [];

		foreach ($appointments as $appointment) {
			$appointmentId = (int) $appointment['appointment_id'];
			$serviceRows = [];

			foreach ($this->serviceIds((string) $appointment['service_id']) as $serviceId) {
				$service = $services[$serviceId] ?? [
					'name' => 'Unavailable service',
					'background' => '#f1faff',
					'color' => '#009ef7',
				];

				$serviceRows[] = new ClientHistoryService(
					id: $serviceId,
					name: (string) $service['name'],
					background: (string) $service['background'],
					color: (string) $service['color'],
					cost: $costs[$appointmentId][$serviceId] ?? null,
				);
			}

			$history[] = new ClientHistoryEntry(
				appointmentId: $appointmentId,
				locationId: (int) $appointment['location_id'],
				locationName: $appointment['location_name'] === null ? null : (string) $appointment['location_name'],
				startDate: (string) $appointment['start_date'],
				endDate: $appointment['end_date'] === null ? null : (string) $appointment['end_date'],
				notes: (string) $appointment['appointment_notes'],
				active: (int) $appointment['status'] === 1,
				services: $serviceRows,
			);
		}

		return $history;
	}

	private function appointmentRows(int $clientId, string $accountId, ?PageRequest $page = null): array
	{
		$db = $this->db();
		$sql = 'SELECT appointments.appointment_id, appointments.location_id,
				COALESCE(
					GROUP_CONCAT(selected_services.service_id ORDER BY selected_services.position ASC, selected_services.service_id ASC),
					appointments.service_id
				) AS service_id,
				appointments.start_date, appointments.end_date,
				appointments.appointment_notes, appointments.status,
				locations.name AS location_name
			FROM Appointments AS appointments
			LEFT JOIN Appointment_Services AS selected_services
				ON selected_services.appointment_id = appointments.appointment_id
				AND selected_services.account_id = appointments.account_id
			LEFT JOIN Business_Locations AS locations
				ON locations.id = appointments.location_id
				AND locations.account_id = appointments.account_id
			WHERE appointments.client_id = ?
			AND appointments.account_id = ?
			GROUP BY appointments.appointment_id, appointments.location_id, appointments.service_id,
				appointments.start_date, appointments.end_date, appointments.appointment_notes,
				appointments.status, locations.name
			ORDER BY appointments.start_date DESC, appointments.appointment_id DESC';

		if ($page === null) {
			$stmt = $db->prepare($sql);
			$stmt->bind_param('is', $clientId, $accountId);
		} else {
			$limit = $page->perPage;
			$offset = $page->offset();
			$stmt = $db->prepare($sql . ' LIMIT ? OFFSET ?');
			$stmt->bind_param('isii', $clientId, $accountId, $limit, $offset);
		}

		$stmt->execute();
		$result = $stmt->get_result();
		$rows = [];

		while ($row = $result->fetch_assoc()) {
			$rows[] = $row;
		}

		$stmt->close();

		return $rows;
	}

	private function appointmentCount(int $clientId, string $accountId): int
	{
		$db = $this->db();
		$stmt = $db->prepare(
			'SELECT COUNT(*) AS total
			FROM Appointments
			WHERE client_id = ?
			AND account_id = ?'
		);
		$stmt->bind_param('is', $clientId, $accountId);
		$stmt->execute();

		$row = $stmt->get_result()->fetch_assoc();
		$stmt->close();

		return (int) ($row['total'] ?? 0);
	}

	private function servicesForAccount(string $accountId): array
	{
		$db = $this->db();
		$stmt = $db->prepare(
			'SELECT id, name, background, color
			FROM Services
			WHERE account_id = ?'
		);
		$stmt->bind_param('s', $accountId);
		$stmt->execute();
		$result = $stmt->get_result();
		$services = [];

		while ($row = $result->fetch_assoc()) {
			$services[(int) $row['id']] = $row;
		}

		$stmt->close();

		return $services;
	}

	/**
	 * @param list<int> $appointmentIds
	 */
	private function costsForAppointments(array $appointmentIds, string $accountId): array
	{
		if ($appointmentIds === []) {
			return [];
		}

		$db = $this->db();
		$placeholders = implode(',', array_fill(0, count($appointmentIds), '?'));
		$stmt = $db->prepare(
			'SELECT costs.appointment_id, costs.service_id, costs.service_cost
			FROM Services_Cost AS costs
			INNER JOIN Appointments AS appointments
				ON appointments.appointment_id = costs.appointment_id
			WHERE costs.appointment_id IN (' . $placeholders . ')
			AND appointments.account_id = ?'
		);
		$params = [...$appointmentIds, $accountId];
		$this->bind($stmt, str_repeat('i', count($appointmentIds)) . 's', ...$params);
		$stmt->execute();
		$result = $stmt->get_result();
		$costs = [];

		while ($row = $result->fetch_assoc()) {
			$costs[(int) $row['appointment_id']][(int) $row['service_id']] = (string) $row['service_cost'];
		}

		$stmt->close();

		return $costs;
	}

	/** @return list<int> */
	private function serviceIds(string $value): array
	{
		$ids = [];

		foreach (explode(',', $value) as $id) {
			$id = (int) trim($id);

			if ($id > 0) {
				$ids[] = $id;
			}
		}

		return $ids;
	}

	private function bind(mysqli_stmt $stmt, string $types, mixed ...$params): void
	{
		$refs = [];
		foreach ($params as $index => $param) {
			$refs[$index] = &$params[$index];
		}

		$stmt->bind_param($types, ...$refs);
	}

	private function db(): mysqli
	{
		return $this->connection->mysqli();
	}
}
