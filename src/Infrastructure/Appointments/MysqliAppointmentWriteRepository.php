<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Appointments;

use Fin\Narekaltro\Domain\Appointments\AppointmentFormData;
use Fin\Narekaltro\Domain\Appointments\AppointmentWriteRepository;
use Fin\Narekaltro\Infrastructure\Database\Connection;
use mysqli;
use Throwable;

final class MysqliAppointmentWriteRepository implements AppointmentWriteRepository
{
	public function __construct(private Connection $connection)
	{
	}

	#[\Override]
	public function create(string $accountId, AppointmentFormData $data): int
	{
		$db = $this->db();
		$status = 1;
		$locationId = $data->locationId;
		$employeeId = $data->employeeId;
		$clientId = $data->clientId;
		$serviceIds = implode(',', $data->serviceIds);
		$startDate = $data->startDate;
		$endDate = $data->endDate;
		$notes = $data->notes;
		$db->begin_transaction();

		try {
			$stmt = $db->prepare(
				'INSERT INTO Appointments (
					account_id, location_id, employee_id, client_id, service_id, start_date, end_date,
					appointment_notes, status
				) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
			);
			$stmt->bind_param(
				'siiissssi',
				$accountId,
				$locationId,
				$employeeId,
				$clientId,
				$serviceIds,
				$startDate,
				$endDate,
				$notes,
				$status
			);
			$stmt->execute();
			$stmt->close();
			$appointmentId = (int) $db->insert_id;

			$this->replaceServices($accountId, $appointmentId, $data->serviceIds);
			$this->saveCosts($appointmentId, $data->costs);
			$db->commit();

			return $appointmentId;
		} catch (Throwable $exception) {
			$db->rollback();
			throw $exception;
		}
	}

	#[\Override]
	public function update(
		int $appointmentId,
		string $accountId,
		AppointmentFormData $data,
		array $editableCostServiceIds
	): void {
		$db = $this->db();
		$locationId = $data->locationId;
		$employeeId = $data->employeeId;
		$serviceIds = implode(',', $data->serviceIds);
		$startDate = $data->startDate;
		$endDate = $data->endDate;
		$notes = $data->notes;
		$db->begin_transaction();

		try {
			$stmt = $db->prepare(
				'UPDATE Appointments
				SET location_id = ?, employee_id = ?, service_id = ?, start_date = ?, end_date = ?, appointment_notes = ?
				WHERE appointment_id = ?
				AND account_id = ?
				AND status = 1'
			);
			$stmt->bind_param(
				'iissssis',
				$locationId,
				$employeeId,
				$serviceIds,
				$startDate,
				$endDate,
				$notes,
				$appointmentId,
				$accountId
			);
			$stmt->execute();
			$stmt->close();

			$this->removeCostsForDeselectedServices($appointmentId, $data->serviceIds);
			$this->replaceServices($accountId, $appointmentId, $data->serviceIds);
			$this->replaceEditableCosts($appointmentId, $data->costs, $editableCostServiceIds);
			$db->commit();
		} catch (Throwable $exception) {
			$db->rollback();
			throw $exception;
		}
	}

	#[\Override]
	public function reschedule(
		int $appointmentId,
		string $accountId,
		string $startDate,
		?string $endDate,
		bool $mayUpdateEndDate
	): void {
		$db = $this->db();

		if ($mayUpdateEndDate) {
			$stmt = $db->prepare(
				'UPDATE Appointments
				SET start_date = ?, end_date = ?
				WHERE appointment_id = ?
				AND account_id = ?
				AND status = 1'
			);
			$stmt->bind_param('ssis', $startDate, $endDate, $appointmentId, $accountId);
		} else {
			$stmt = $db->prepare(
				'UPDATE Appointments
				SET start_date = ?
				WHERE appointment_id = ?
				AND account_id = ?
				AND status = 1'
			);
			$stmt->bind_param('sis', $startDate, $appointmentId, $accountId);
		}

		$stmt->execute();
		$stmt->close();
	}

	#[\Override]
	public function cancel(int $appointmentId, string $accountId): void
	{
		$db = $this->db();
		$status = 0;
		$stmt = $db->prepare(
			'UPDATE Appointments
			SET status = ?
			WHERE appointment_id = ?
			AND account_id = ?
			AND status = 1'
		);
		$stmt->bind_param('iis', $status, $appointmentId, $accountId);
		$stmt->execute();
		$stmt->close();
	}

	/** @param array<int, string> $costs */
	private function saveCosts(int $appointmentId, array $costs): void
	{
		foreach ($costs as $serviceId => $cost) {
			$this->upsertCost($appointmentId, (int) $serviceId, $cost);
		}
	}

	/** @param list<int> $serviceIds */
	private function replaceServices(string $accountId, int $appointmentId, array $serviceIds): void
	{
		$stmt = $this->db()->prepare(
			'DELETE FROM Appointment_Services
			WHERE appointment_id = ?
			AND account_id = ?'
		);
		$stmt->bind_param('is', $appointmentId, $accountId);
		$stmt->execute();
		$stmt->close();

		foreach (array_values($serviceIds) as $position => $serviceId) {
			$this->insertService($accountId, $appointmentId, (int) $serviceId, $position + 1);
		}
	}

	private function insertService(string $accountId, int $appointmentId, int $serviceId, int $position): void
	{
		$stmt = $this->db()->prepare(
			'INSERT INTO Appointment_Services (account_id, appointment_id, service_id, position, date_added)
			VALUES (?, ?, ?, ?, NOW())
			ON DUPLICATE KEY UPDATE position = VALUES(position)'
		);
		$stmt->bind_param('siii', $accountId, $appointmentId, $serviceId, $position);
		$stmt->execute();
		$stmt->close();
	}

	/** @param list<int> $selectedServiceIds */
	private function removeCostsForDeselectedServices(int $appointmentId, array $selectedServiceIds): void
	{
		if ($selectedServiceIds === []) {
			$stmt = $this->db()->prepare(
				'DELETE FROM Services_Cost
				WHERE appointment_id = ?'
			);
			$stmt->bind_param('i', $appointmentId);
			$stmt->execute();
			$stmt->close();

			return;
		}

		$ids = implode(',', array_map('intval', $selectedServiceIds));
		$stmt = $this->db()->prepare(
			"DELETE FROM Services_Cost
			WHERE appointment_id = ?
			AND service_id NOT IN ({$ids})"
		);
		$stmt->bind_param('i', $appointmentId);
		$stmt->execute();
		$stmt->close();
	}

	/**
	 * @param array<int, string> $costs
	 * @param list<int> $editableCostServiceIds
	 */
	private function replaceEditableCosts(int $appointmentId, array $costs, array $editableCostServiceIds): void
	{
		foreach ($editableCostServiceIds as $serviceId) {
			if (!isset($costs[$serviceId])) {
				$stmt = $this->db()->prepare(
					'DELETE FROM Services_Cost
					WHERE appointment_id = ?
					AND service_id = ?'
				);
				$stmt->bind_param('ii', $appointmentId, $serviceId);
				$stmt->execute();
				$stmt->close();
				continue;
			}

			$this->upsertCost($appointmentId, $serviceId, $costs[$serviceId]);
		}
	}

	private function upsertCost(int $appointmentId, int $serviceId, string $cost): void
	{
		$stmt = $this->db()->prepare(
			'INSERT INTO Services_Cost (appointment_id, service_id, service_cost, date_added)
			VALUES (?, ?, ?, NOW())
			ON DUPLICATE KEY UPDATE service_cost = VALUES(service_cost), date_modified = NOW()'
		);
		$stmt->bind_param('iis', $appointmentId, $serviceId, $cost);
		$stmt->execute();
		$stmt->close();
	}

	private function db(): mysqli
	{
		return $this->connection->mysqli();
	}
}
