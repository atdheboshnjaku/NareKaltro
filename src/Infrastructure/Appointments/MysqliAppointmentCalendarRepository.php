<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Appointments;

use DateTimeImmutable;
use Fin\Narekaltro\Domain\Appointments\AppointmentCalendarRepository;
use Fin\Narekaltro\Domain\Appointments\AppointmentClient;
use Fin\Narekaltro\Domain\Appointments\AppointmentScope;
use Fin\Narekaltro\Domain\Appointments\AppointmentService;
use Fin\Narekaltro\Domain\Appointments\ScheduledAppointment;
use Fin\Narekaltro\Infrastructure\Database\Connection;
use mysqli;

final class MysqliAppointmentCalendarRepository implements AppointmentCalendarRepository
{
	public function __construct(private Connection $connection)
	{
	}

	#[\Override]
	public function activeForAccount(
		AppointmentScope $scope,
		?DateTimeImmutable $rangeStart = null,
		?DateTimeImmutable $rangeEnd = null
	): array {
		$db = $this->db();
		$accountId = $scope->accountId;
		$locationSql = $this->locationConstraint($scope, 'appointments.location_id');
		$employeeSql = $this->employeeConstraint($scope, 'appointments.employee_id');
		$sql = $this->selectWithCosts()
			. ' WHERE appointments.account_id = ?
			AND appointments.status = 1'
			. $locationSql
			. $employeeSql;

		if ($rangeStart !== null && $rangeEnd !== null) {
			$sql .= ' AND appointments.start_date < ?
				AND (
					(
						appointments.end_date IS NOT NULL
						AND appointments.end_date >= appointments.start_date
						AND appointments.end_date >= ?
					)
					OR appointments.start_date >= ?
				)';
			$stmt = $db->prepare($sql . ' ORDER BY appointments.start_date ASC, appointment_services.position ASC, appointment_services.service_id ASC');
			$start = $rangeStart->format('Y-m-d H:i:s');
			$end = $rangeEnd->format('Y-m-d H:i:s');
			$stmt->bind_param('ssss', $accountId, $end, $start, $start);
		} else {
			$stmt = $db->prepare($sql . ' ORDER BY appointments.start_date ASC, appointment_services.position ASC, appointment_services.service_id ASC');
			$stmt->bind_param('s', $accountId);
		}

		$stmt->execute();
		$rows = $this->rows($stmt->get_result());
		$stmt->close();

		return $this->appointmentsFromRows($rows, $accountId);
	}

	#[\Override]
	public function lastForClient(int $clientId, AppointmentScope $scope, int $limit): array
	{
		$db = $this->db();
		$accountId = $scope->accountId;
		$limit = max(1, min(10, $limit));
		$locationSql = $this->locationConstraint($scope, 'location_id');
		$employeeSql = $this->employeeConstraint($scope, 'employee_id');
		$stmt = $db->prepare(
			'SELECT recent.appointment_id, recent.client_id, recent.location_id, recent.employee_id,
				recent.service_id, appointment_services.service_id AS selected_service_id,
				recent.start_date, recent.end_date, recent.appointment_notes, recent.status,
				clients.name AS client_name, locations.name AS location_name,
				employees.name AS employee_name,
				costs.service_id AS cost_service_id, costs.service_cost
			FROM (
				SELECT appointment_id, account_id, client_id, location_id, employee_id, service_id,
					start_date, end_date, appointment_notes, status
				FROM Appointments
				WHERE client_id = ?
				AND account_id = ?' . $locationSql . $employeeSql . '
				ORDER BY start_date DESC
				LIMIT ?
			) AS recent
			LEFT JOIN Users AS clients
				ON clients.id = recent.client_id
				AND clients.account_id = recent.account_id
				AND clients.role_id = 1
			LEFT JOIN Business_Locations AS locations
				ON locations.id = recent.location_id
				AND locations.account_id = recent.account_id
			LEFT JOIN Users AS employees
				ON employees.id = recent.employee_id
				AND employees.account_id = recent.account_id
				AND employees.role_id > 1
			LEFT JOIN Appointment_Services AS appointment_services
				ON appointment_services.appointment_id = recent.appointment_id
				AND appointment_services.account_id = recent.account_id
			LEFT JOIN Services_Cost AS costs
				ON costs.appointment_id = recent.appointment_id
				AND (costs.service_id = appointment_services.service_id OR appointment_services.service_id IS NULL)
			ORDER BY recent.start_date DESC, appointment_services.position ASC, appointment_services.service_id ASC'
		);
		$stmt->bind_param('isi', $clientId, $accountId, $limit);
		$stmt->execute();
		$rows = $this->rows($stmt->get_result());
		$stmt->close();

		return $this->appointmentsFromRows($rows, $accountId);
	}

	#[\Override]
	public function clientForAccount(int $clientId, string $accountId): ?AppointmentClient
	{
		$db = $this->db();
		$stmt = $db->prepare(
			'SELECT id, name
			FROM Users
			WHERE id = ?
			AND account_id = ?
			AND role_id = 1
			LIMIT 1'
		);
		$stmt->bind_param('is', $clientId, $accountId);
		$stmt->execute();
		$row = $stmt->get_result()->fetch_assoc() ?: null;
		$stmt->close();

		if ($row === null) {
			return null;
		}

		$name = trim((string) ($row['name'] ?? ''));

		return new AppointmentClient(
			id: (int) $row['id'],
			name: $name === '' ? 'Unavailable client' : $name
		);
	}

	#[\Override]
	public function upcomingCount(AppointmentScope $scope): int
	{
		$db = $this->db();
		$accountId = $scope->accountId;
		$locationSql = $this->locationConstraint($scope, 'location_id');
		$employeeSql = $this->employeeConstraint($scope, 'employee_id');
		$stmt = $db->prepare(
			'SELECT COUNT(*) AS total
			FROM Appointments
			WHERE account_id = ?
			AND status = 1
			AND start_date >= NOW()' . $locationSql . $employeeSql
		);
		$stmt->bind_param('s', $accountId);
		$stmt->execute();
		$row = $stmt->get_result()->fetch_assoc();
		$stmt->close();

		return (int) ($row['total'] ?? 0);
	}

	#[\Override]
	public function findActiveForAccount(int $appointmentId, AppointmentScope $scope): ?ScheduledAppointment
	{
		$db = $this->db();
		$accountId = $scope->accountId;
		$locationSql = $this->locationConstraint($scope, 'appointments.location_id');
		$employeeSql = $this->employeeConstraint($scope, 'appointments.employee_id');
		$stmt = $db->prepare(
			$this->selectWithCosts() . '
			WHERE appointments.appointment_id = ?
			AND appointments.account_id = ?
			AND appointments.status = 1' . $locationSql . $employeeSql . '
			ORDER BY appointment_services.position ASC, appointment_services.service_id ASC'
		);
		$stmt->bind_param('is', $appointmentId, $accountId);
		$stmt->execute();
		$appointments = $this->appointmentsFromRows($this->rows($stmt->get_result()), $accountId);
		$stmt->close();

		return $appointments[0] ?? null;
	}

	private function selectWithCosts(): string
	{
		return 'SELECT appointments.appointment_id, appointments.client_id, appointments.location_id,
				appointments.employee_id, appointments.service_id, appointments.start_date, appointments.end_date,
				appointments.appointment_notes, appointments.status,
				clients.name AS client_name, locations.name AS location_name,
				employees.name AS employee_name,
				appointment_services.service_id AS selected_service_id,
				costs.service_id AS cost_service_id, costs.service_cost
			FROM Appointments AS appointments
			LEFT JOIN Users AS clients
				ON clients.id = appointments.client_id
				AND clients.account_id = appointments.account_id
				AND clients.role_id = 1
			LEFT JOIN Business_Locations AS locations
				ON locations.id = appointments.location_id
				AND locations.account_id = appointments.account_id
			LEFT JOIN Users AS employees
				ON employees.id = appointments.employee_id
				AND employees.account_id = appointments.account_id
				AND employees.role_id > 1
			LEFT JOIN Appointment_Services AS appointment_services
				ON appointment_services.appointment_id = appointments.appointment_id
				AND appointment_services.account_id = appointments.account_id
			LEFT JOIN Services_Cost AS costs
				ON costs.appointment_id = appointments.appointment_id
				AND (costs.service_id = appointment_services.service_id OR appointment_services.service_id IS NULL)';
	}

	private function appointmentsFromRows(array $rows, string $accountId): array
	{
		$services = $this->servicesForAccount($accountId);
		$appointments = [];
		$orderedIds = [];

		foreach ($rows as $row) {
			$id = (int) $row['appointment_id'];
			if (!isset($appointments[$id])) {
				$orderedIds[] = $id;
				$appointments[$id] = [
					'row' => $row,
					'costs' => [],
					'service_ids' => [],
				];
			}

			$selectedServiceId = (int) ($row['selected_service_id'] ?? 0);
			if ($selectedServiceId > 0) {
				$appointments[$id]['service_ids'][$selectedServiceId] = $selectedServiceId;
			}

			$costServiceId = (int) ($row['cost_service_id'] ?? 0);
			if ($costServiceId > 0) {
				$appointments[$id]['costs'][$costServiceId] = (string) $row['service_cost'];
			}
		}

		$result = [];
		foreach ($orderedIds as $id) {
			$row = $appointments[$id]['row'];
			$costs = $appointments[$id]['costs'];
			$serviceIds = $appointments[$id]['service_ids'] === []
				? $this->serviceIds((string) $row['service_id'])
				: array_values($appointments[$id]['service_ids']);
			$selectedServices = [];

			foreach ($serviceIds as $serviceId) {
				$service = $services[$serviceId] ?? [
					'name' => 'Unavailable service',
					'background' => '#f1faff',
					'color' => '#009ef7',
				];
				$selectedServices[] = new AppointmentService(
					id: $serviceId,
					name: (string) $service['name'],
					background: (string) ($service['background'] ?: '#f1faff'),
					color: (string) ($service['color'] ?: '#009ef7'),
					cost: $costs[$serviceId] ?? null
				);
			}

			$clientName = trim((string) ($row['client_name'] ?? ''));
			$result[] = new ScheduledAppointment(
				id: $id,
				clientId: (int) $row['client_id'],
				clientName: $clientName === '' ? 'Unavailable client' : $clientName,
				locationId: (int) $row['location_id'],
				locationName: $row['location_name'] === null ? null : (string) $row['location_name'],
				employeeId: $row['employee_id'] === null ? null : (int) $row['employee_id'],
				employeeName: $row['employee_name'] === null ? null : (string) $row['employee_name'],
				startDate: (string) $row['start_date'],
				endDate: $row['end_date'] === null ? null : (string) $row['end_date'],
				notes: (string) $row['appointment_notes'],
				active: (int) $row['status'] === 1,
				services: $selectedServices
			);
		}

		return $result;
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

	/** @return list<int> */
	private function serviceIds(string $csv): array
	{
		$ids = [];
		foreach (explode(',', $csv) as $candidate) {
			$id = (int) trim($candidate);
			if ($id > 0) {
				$ids[$id] = $id;
			}
		}

		return array_values($ids);
	}

	private function rows(\mysqli_result $result): array
	{
		$rows = [];
		while ($row = $result->fetch_assoc()) {
			$rows[] = $row;
		}

		return $rows;
	}

	private function locationConstraint(AppointmentScope $scope, string $column): string
	{
		if ($scope->isAccountWide()) {
			return '';
		}

		if (!$scope->hasVisibleLocations()) {
			return ' AND 1 = 0';
		}

		return ' AND ' . $column . ' IN (' . implode(',', $scope->locationIds) . ')';
	}

	private function employeeConstraint(AppointmentScope $scope, string $column): string
	{
		if (!$scope->hasEmployeeFilter()) {
			return '';
		}

		return ' AND ' . $column . ' = ' . $scope->employeeId;
	}

	private function db(): mysqli
	{
		return $this->connection->mysqli();
	}
}
