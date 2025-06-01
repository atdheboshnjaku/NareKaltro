<?php

declare(strict_types=1);

namespace Fin\Narekaltro\App;

class Appointments extends Database
{

	private $table = "Appointments";
	private $table_2 = "Users";
	private $table_3 = "Services";
	private $table_4 = "Business_Locations";
	private $table_5 = "Services_Cost";

	public int $id = 0;

	public function getColumnName(): array
	{

		return $this->getTableColumnName($this->table);

	}

	public function getCreatedAppointmentId(): int
	{
		return $this->lastId();
	}

	public function getAppointmentsJSON(?string $accountID): ?string
	{
		$columnName = $this->getColumnName();
		$appointmentList = [];
		$sql = "SELECT * FROM {$this->table} WHERE `account_id` = '" . $this->escape($accountID) . "' AND status = 1";
		$appointments = $this->fetchAll($sql);
		if ($appointments) {
			foreach ($appointments as $appointment):
				$user = $this->getUser((int) $appointment['client_id']);
				$service = $this->getService((int) $appointment['service_id']);
				$location = $this->getLocation((int) $appointment['location_id']);
				$costMap = $this->getServiceCostsByAppointment((int) $appointment[$columnName['COLUMN_NAME']]);

				if ($appointment['end_date'] != '') {

					$appointmentList[] = [
						'id' => (int) $appointment[$columnName['COLUMN_NAME']],
						'title' => $user['name'],
						'extendedProps' => [
							'location_id' => $location['id'],
							'location' => $location['name'],
							'service_id' => $appointment['service_id'],
							'service' => $service['name'],
							'service_costs' => $costMap,
							'notes' => $appointment['appointment_notes']
						],
						'start' => $appointment['start_date'],
						'end' => $appointment['end_date'],
						//'url'       => '/appointment/edit?id=' . (int) $appointment[$columnName['COLUMN_NAME']],
						'color' => $service['background'],
						'textColor' => $service['color']

					];
				} else {
					$appointmentList[] = [
						'id' => (int) $appointment[$columnName['COLUMN_NAME']],
						'title' => $user['name'],
						'extendedProps' => [
							'location_id' => $location['id'],
							'location' => $location['name'],
							'service_id' => $appointment['service_id'],
							'service' => $service['name'],
							'service_costs' => $costMap,
							'notes' => $appointment['appointment_notes']
						],
						'start' => $appointment['start_date'],
						//'url'       => '/appointment/edit?id=' . (int) $appointment[$columnName['COLUMN_NAME']],
						'color' => $service['background'],
						'textColor' => $service['color']
					];
				}

			endforeach;

			return json_encode($appointmentList);
		} else {
			return "";
		}

	}

	public function addAppointment(?array $params): bool
	{

		if (!empty($params)) {

			$params['status'] = "1";
			$this->prepareToInsert($params);
			$out = $this->insert($this->table);
			if ($out) {
				$this->id = $this->lastId(); // 👈 crucial
			}
			return $out;

		}
		return false;


	}

	public function getUser(int $id): array
	{

		$sql = "SELECT `name` FROM {$this->table_2}
                WHERE `id` = '" . (int) $id . "'";
		return $this->fetchOne($sql);

	}

	public function getService(int $id): array
	{

		$sql = "SELECT `id`, `name`, `background`, `color` FROM {$this->table_3}
                WHERE `id` = '" . (int) $id . "'";
		return $this->fetchOne($sql);

	}

	public function getServiceIDs(int $id): array
	{

		$sql = "SELECT `id`, `name`, `background`, `color` FROM {$this->table_3}
                WHERE `id` = '" . (int) $id . "'";
		return $this->fetchOne($sql);

	}

	public function getLocation(int $id): ?array
	{

		$sql = "SELECT `id`, `name` FROM {$this->table_4}
                WHERE `id` = '" . (int) $id . "'";
		return $this->fetchOne($sql);

	}

	public function updateAppointmentDate(array $params, int $app_id): bool
	{


		if (!empty($params)) {
			$this->prepareToUpdate($params);
			return $this->update($this->table, $app_id);
		}


	}

	public function saveServiceCosts(int $appointmentId, array $costs): void
	{
		// Step 1: Remove services not in current selection
		$serviceIds = array_map('intval', array_keys($costs));
		$inList = implode(',', $serviceIds ?: [0]); // [0] ensures valid SQL if empty
		$sqlDelete = "DELETE FROM {$this->table_5}
					WHERE appointment_id = '" . $this->escape($appointmentId) . "'
					AND service_id NOT IN ($inList)";
		$this->query($sqlDelete);

		// Step 2: Upsert new/updated service costs
		foreach ($costs as $serviceId => $serviceCost) {
			$serviceId = (int)$serviceId;
			$serviceCost = floatval($serviceCost);

			if ($serviceCost > 0) {
				$sql = "INSERT INTO {$this->table_5} (appointment_id, service_id, service_cost, date_added)
						VALUES (
							'" . $this->escape($appointmentId) . "',
							'" . $this->escape($serviceId) . "',
							'" . $this->escape($serviceCost) . "',
							NOW()
						)
						ON DUPLICATE KEY UPDATE service_cost = VALUES(service_cost), date_modified = NOW()";

				$this->query($sql);
			}
		}
	}

	public function updateServiceCosts(int $appointmentId, array $costs): void
	{
		global $logger;

		foreach ($costs as $serviceId => $serviceCost) {
			$serviceId = (int)$serviceId;
			$serviceCost = floatval($serviceCost);

			$sql = "UPDATE {$this->table_5}
					SET service_cost = '" . $this->escape($serviceCost) . "',
						date_modified = NOW()
					WHERE appointment_id = '" . $this->escape($appointmentId) . "'
					AND service_id = '" . $this->escape($serviceId) . "'";
			$this->query($sql);
		}
	}

	public function getServiceCostsByAppointment(int $appointmentId): array
	{
		$sql = "SELECT service_id, service_cost
				FROM {$this->table_5}
				WHERE appointment_id = '" . $this->escape($appointmentId) . "'";

		$results = $this->fetchAll($sql);

		$costMap = [];
		foreach ($results as $row) {
			$costMap[$row['service_id']] = $row['service_cost'];
		}

		return $costMap;
	}

	public function deleteServiceCostByAppointmentId(int $id)
	{
		$this->db->query("DELETE FROM Services_Cost WHERE appointment_id = " . (int)$id);
	}

	public function cancelAppointment(int $id): bool
	{

		if (!empty($id)) {

			$safeID = (int)$id;
			if ($this->deactivateStatus($this->table, $safeID)) {
				$this->deleteServiceCostByAppointmentId($safeID);
				return true;
			}

		}

		return false;

	}

	public function numberOfUpcomingAppointments(string $accountID): array
	{

		$sql = "
		SELECT
		  COUNT(*) FROM {$this->table}
		WHERE `start_date` >= NOW()
		AND `account_id` = '" . $this->escape($accountID) . "'
		  AND `status` = 1
		    AND NOT `status` = 0";
		return $this->fetchOne($sql);

	}

	public function getClientHistory(int $clientID) : array {
		$sql = "
		SELECT
		  appointment_id,
		  location_id,
		  service_id,
		  start_date,
		  end_date,
		  appointment_notes,
		  status
		FROM " . $this->table . "
		WHERE `client_id` = '" . (int)$clientID . "'
		ORDER BY `start_date` DESC";

		return $this->fetchAll($sql);
	}

	public function totalClientAppointments(int $clientID) : array {
		$sql = "
		SELECT COUNT(*) AS total FROM " . $this->table . "
		WHERE `client_id` = '" . (int)$clientID . "'";

		return $this->fetchOne($sql);
	}

}