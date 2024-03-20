<?php

declare(strict_types=1);

namespace Fin\Narekaltro\App;

class Appointments extends Database
{

	private $table = "Appointments";
	private $table_2 = "Users";
	private $table_3 = "Services";
	private $table_4 = "Business_Locations";

	public function getColumnName(): array
	{

		return $this->getTableColumnName($this->table);

	}

	public function getAppointmentsJSON(?string $accountID): ?string
	{

		$columnName = $this->getColumnName();
		$appointmentList = [];
		$sql = "SELECT * FROM {$this->table}
                WHERE `account_id` = '" . $this->escape($accountID) . "'
                AND status = 1";
		$appointments = $this->fetchAll($sql);
		if ($appointments) {
			foreach ($appointments as $appointment):
				$user = $this->getUser((int) $appointment['client_id']);
				$service = $this->getService((int) $appointment['service_id']);
				$location = $this->getLocation((int) $appointment['location_id']);

				if ($appointment['end_date'] != '') {

					$appointmentList[] = [
						'id' => (int) $appointment[$columnName['COLUMN_NAME']],
						'title' => $user['name'],
						'extendedProps' => [
							'location_id' => $location['id'],
							'location' => $location['name'],
							//'service_id' => $service['id'],
							'service_id' => $appointment['service_id'],
							'service' => $service['name'],
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

	public function addAppointment(array $params = null): bool
	{

		if (!empty($params)) {

			$params['status'] = "1";
			$this->prepareToInsert($params);
			$out = $this->insert($this->table);
			$this->id = $this->id;
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

	// public function updateAppointmentDate($appointment_id, $start_date, $end_date): bool
	// {


	//     $sql = "UPDATE {$this->table}
	//             SET `start_date` = '". $this->escape($start_date) ."',
	//             `end_date` = '". $this->escape($end_date) ."'
	//             WHERE `appointment_id` = '". $this->escape($appointment_id) ."'";
	//             return $this->query($sql);


	// }

	public function updateAppointmentDate(array $params, int $app_id): bool
	{


		if (!empty($params)) {
			$this->prepareToUpdate($params);
			return $this->update($this->table, $app_id);
		}


	}

	public function cancelAppointment(int $id): bool
	{

		if (!empty($id)) {

			$safeID = (int) $id;
			return $this->deactivateStatus($this->table, $safeID);

		}

	}

	public function numberOfUpcomingAppointments(string $accountID): array
	{

		$sql = "SELECT COUNT(*) FROM {$this->table}
                WHERE `start_date` >= NOW()
                AND `account_id` = '" . $this->escape($accountID) . "'
                AND `status` = 1
                AND NOT `status` = 0";
		return $this->fetchOne($sql);

	}

	public function getClientHistory(int $clientID) : array {
		$sql = "
		SELECT
		  location_id,
		  service_id,
		  start_date,
		  end_date,
		  appointment_notes
		FROM " . $this->table . "
		WHERE `client_id` = '" . (int)$clientID . "'
		ORDER BY `start_date` DESC";

		return $this->fetchAll($sql);
	}

	public function totalClientAppointments(int $clientID) : array {
		$sql = "
		SELECT COUNT(*) AS total FROM " . $this->table . "
		WHERE `client_id` = '" . (int)$clientID . "'";
		//var_dump($sql);die;
		return $this->fetchOne($sql);
	}

}