<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Reports;

use Fin\Narekaltro\Domain\Reports\ReportCostEntry;
use Fin\Narekaltro\Domain\Reports\ReportRepository;
use Fin\Narekaltro\Domain\Reports\ReportScope;
use Fin\Narekaltro\Infrastructure\Database\Connection;
use mysqli;

final class MysqliReportRepository implements ReportRepository
{
	public function __construct(private Connection $connection)
	{
	}

	#[\Override]
	public function appointmentMonthlyTotals(
		ReportScope $scope,
		int $fromYear,
		int $toYear,
		bool $cancelled
	): array {
		$accountId = $scope->accountId;
		$status = $cancelled ? 0 : 1;
		[$fromDate, $untilDate] = $this->period($fromYear, $toYear);
		$locationSql = $this->locationConstraint($scope, 'location_id');
		$employeeSql = $this->employeeConstraint($scope, 'employee_id');
		$stmt = $this->db()->prepare(
			'SELECT YEAR(start_date) AS report_year, MONTH(start_date) AS report_month, COUNT(*) AS total
			FROM Appointments
			WHERE account_id = ?
			AND status = ?
			AND start_date >= ?
			AND start_date < ?' . $locationSql . $employeeSql . '
			GROUP BY YEAR(start_date), MONTH(start_date)'
		);
		$stmt->bind_param('siss', $accountId, $status, $fromDate, $untilDate);

		return $this->groupedTotals($stmt);
	}

	#[\Override]
	public function newClientMonthlyTotals(ReportScope $scope, int $fromYear, int $toYear): array
	{
		$accountId = $scope->accountId;
		[$fromDate, $untilDate] = $this->period($fromYear, $toYear);

		if ($scope->hasEmployeeFilter()) {
			$locationSql = $this->locationConstraint($scope, 'appointments.location_id');
			$employeeSql = $this->employeeConstraint($scope, 'appointments.employee_id');
			$stmt = $this->db()->prepare(
				'SELECT YEAR(clients.date) AS report_year, MONTH(clients.date) AS report_month,
					COUNT(DISTINCT clients.id) AS total
				FROM Users AS clients
				INNER JOIN Appointments AS appointments
					ON appointments.client_id = clients.id
					AND appointments.account_id = clients.account_id
					AND appointments.status = 1
				WHERE clients.account_id = ?
				AND clients.role_id = 1
				AND clients.date >= ?
				AND clients.date < ?' . $locationSql . $employeeSql . '
				GROUP BY YEAR(clients.date), MONTH(clients.date)'
			);
			$stmt->bind_param('sss', $accountId, $fromDate, $untilDate);

			return $this->groupedTotals($stmt);
		}

		$locationSql = $this->locationConstraint($scope, 'location_id');
		$stmt = $this->db()->prepare(
			'SELECT YEAR(date) AS report_year, MONTH(date) AS report_month, COUNT(*) AS total
			FROM Users
			WHERE account_id = ?
			AND role_id = 1
			AND date >= ?
			AND date < ?' . $locationSql . '
			GROUP BY YEAR(date), MONTH(date)'
		);
		$stmt->bind_param('sss', $accountId, $fromDate, $untilDate);

		return $this->groupedTotals($stmt);
	}

	#[\Override]
	public function activeCostEntries(ReportScope $scope, int $fromYear, int $toYear): array
	{
		$accountId = $scope->accountId;
		[$fromDate, $untilDate] = $this->period($fromYear, $toYear);
		$locationSql = $this->locationConstraint($scope, 'appointments.location_id');
		$employeeSql = $this->employeeConstraint($scope, 'appointments.employee_id');
		$stmt = $this->db()->prepare(
			'SELECT appointments.appointment_id, appointments.location_id, appointments.employee_id, costs.service_id,
				YEAR(appointments.start_date) AS report_year,
				MONTH(appointments.start_date) AS report_month,
				costs.service_cost
			FROM Appointments AS appointments
			INNER JOIN Services_Cost AS costs
				ON costs.appointment_id = appointments.appointment_id
			INNER JOIN Services AS services
				ON services.id = costs.service_id
				AND services.account_id = appointments.account_id
				AND services.quote_only = 0
			WHERE appointments.account_id = ?
			AND appointments.status = 1
			AND appointments.start_date >= ?
			AND appointments.start_date < ?' . $locationSql . $employeeSql . '
			ORDER BY appointments.start_date ASC'
		);
		$stmt->bind_param('sss', $accountId, $fromDate, $untilDate);
		$stmt->execute();
		$result = $stmt->get_result();
		$entries = [];

		while ($row = $result->fetch_assoc()) {
			$entries[] = new ReportCostEntry(
				appointmentId: (int) $row['appointment_id'],
				locationId: (int) $row['location_id'],
				employeeId: $row['employee_id'] === null ? null : (int) $row['employee_id'],
				serviceId: (int) $row['service_id'],
				year: (int) $row['report_year'],
				month: (int) $row['report_month'],
				value: (string) $row['service_cost']
			);
		}

		$stmt->close();

		return $entries;
	}

	#[\Override]
	public function activeClientCount(ReportScope $scope): int
	{
		$accountId = $scope->accountId;

		if ($scope->hasEmployeeFilter()) {
			$locationSql = $this->locationConstraint($scope, 'appointments.location_id');
			$employeeSql = $this->employeeConstraint($scope, 'appointments.employee_id');
			$stmt = $this->db()->prepare(
				'SELECT COUNT(DISTINCT clients.id) AS total
				FROM Users AS clients
				INNER JOIN Appointments AS appointments
					ON appointments.client_id = clients.id
					AND appointments.account_id = clients.account_id
					AND appointments.status = 1
				WHERE clients.account_id = ?
				AND clients.role_id = 1
				AND clients.status = 1' . $locationSql . $employeeSql
			);
			$stmt->bind_param('s', $accountId);
			$stmt->execute();
			$row = $stmt->get_result()->fetch_assoc();
			$stmt->close();

			return (int) ($row['total'] ?? 0);
		}

		$locationSql = $this->locationConstraint($scope, 'location_id');
		$stmt = $this->db()->prepare(
			'SELECT COUNT(*) AS total
			FROM Users
			WHERE account_id = ?
			AND role_id = 1
			AND status = 1' . $locationSql
		);
		$stmt->bind_param('s', $accountId);
		$stmt->execute();
		$row = $stmt->get_result()->fetch_assoc();
		$stmt->close();

		return (int) ($row['total'] ?? 0);
	}

	#[\Override]
	public function activeAppointmentCountBetween(ReportScope $scope, string $fromDate, string $untilDate): int
	{
		$accountId = $scope->accountId;
		$locationSql = $this->locationConstraint($scope, 'location_id');
		$employeeSql = $this->employeeConstraint($scope, 'employee_id');
		$stmt = $this->db()->prepare(
			'SELECT COUNT(*) AS total
			FROM Appointments
			WHERE account_id = ?
			AND status = 1
			AND start_date >= ?
			AND start_date < ?' . $locationSql . $employeeSql
		);
		$stmt->bind_param('sss', $accountId, $fromDate, $untilDate);
		$stmt->execute();
		$row = $stmt->get_result()->fetch_assoc();
		$stmt->close();

		return (int) ($row['total'] ?? 0);
	}

	#[\Override]
	public function upcomingAppointmentCount(ReportScope $scope, string $fromDate): int
	{
		$accountId = $scope->accountId;
		$locationSql = $this->locationConstraint($scope, 'location_id');
		$employeeSql = $this->employeeConstraint($scope, 'employee_id');
		$stmt = $this->db()->prepare(
			'SELECT COUNT(*) AS total
			FROM Appointments
			WHERE account_id = ?
			AND status = 1
			AND start_date >= ?' . $locationSql . $employeeSql
		);
		$stmt->bind_param('ss', $accountId, $fromDate);
		$stmt->execute();
		$row = $stmt->get_result()->fetch_assoc();
		$stmt->close();

		return (int) ($row['total'] ?? 0);
	}

	#[\Override]
	public function serviceDemandTotals(ReportScope $scope, int $year): array
	{
		$accountId = $scope->accountId;
		[$fromDate, $untilDate] = $this->period($year, $year);
		$locationSql = $this->locationConstraint($scope, 'appointments.location_id');
		$employeeSql = $this->employeeConstraint($scope, 'appointments.employee_id');
		$stmt = $this->db()->prepare(
			"SELECT services.id, services.name, services.background, services.color, services.quote_only,
				COUNT(DISTINCT appointments.appointment_id) AS appointment_total
			FROM Appointments AS appointments
			INNER JOIN Appointment_Services AS appointment_services
				ON appointment_services.appointment_id = appointments.appointment_id
				AND appointment_services.account_id = appointments.account_id
			INNER JOIN Services AS services
				ON services.id = appointment_services.service_id
				AND services.account_id = appointments.account_id
			WHERE appointments.account_id = ?
			AND appointments.status = 1
			AND appointments.start_date >= ?
			AND appointments.start_date < ?" . $locationSql . $employeeSql . "
			GROUP BY services.id, services.name, services.background, services.color, services.quote_only
			ORDER BY appointment_total DESC, services.name ASC"
		);
		$stmt->bind_param('sss', $accountId, $fromDate, $untilDate);
		$stmt->execute();
		$result = $stmt->get_result();
		$totals = [];

		while ($row = $result->fetch_assoc()) {
			$totals[] = [
				'id' => (int) $row['id'],
				'name' => (string) $row['name'],
				'background' => (string) $row['background'],
				'color' => (string) $row['color'],
				'quoteOnly' => (int) $row['quote_only'] === 1,
				'appointments' => (int) $row['appointment_total'],
			];
		}

		$stmt->close();

		return $totals;
	}

	#[\Override]
	public function locationDemandTotals(ReportScope $scope, int $year): array
	{
		$accountId = $scope->accountId;
		[$fromDate, $untilDate] = $this->period($year, $year);
		$locationSql = $this->locationConstraint($scope, 'appointments.location_id');
		$employeeSql = $this->employeeConstraint($scope, 'appointments.employee_id');
		$stmt = $this->db()->prepare(
			'SELECT locations.id, locations.name, COUNT(*) AS appointment_total
			FROM Appointments AS appointments
			INNER JOIN Business_Locations AS locations
				ON locations.id = appointments.location_id
				AND locations.account_id = appointments.account_id
			WHERE appointments.account_id = ?
			AND appointments.status = 1
			AND appointments.start_date >= ?
			AND appointments.start_date < ?' . $locationSql . $employeeSql . '
			GROUP BY locations.id, locations.name
			ORDER BY appointment_total DESC, locations.name ASC'
		);
		$stmt->bind_param('sss', $accountId, $fromDate, $untilDate);
		$stmt->execute();
		$result = $stmt->get_result();
		$totals = [];

		while ($row = $result->fetch_assoc()) {
			$totals[] = [
				'id' => (int) $row['id'],
				'name' => (string) $row['name'],
				'appointments' => (int) $row['appointment_total'],
			];
		}

		$stmt->close();

		return $totals;
	}

	#[\Override]
	public function employeeDemandTotals(ReportScope $scope, int $year): array
	{
		$accountId = $scope->accountId;
		[$fromDate, $untilDate] = $this->period($year, $year);
		$locationSql = $this->locationConstraint($scope, 'appointments.location_id');
		$employeeSql = $this->employeeConstraint($scope, 'appointments.employee_id');
		$stmt = $this->db()->prepare(
			"SELECT COALESCE(employees.id, 0) AS employee_id,
				COALESCE(NULLIF(employees.name, ''), 'Unassigned') AS employee_name,
				COUNT(*) AS appointment_total
			FROM Appointments AS appointments
			LEFT JOIN Users AS employees
				ON employees.id = appointments.employee_id
				AND employees.account_id = appointments.account_id
				AND employees.role_id > 1
			WHERE appointments.account_id = ?
			AND appointments.status = 1
			AND appointments.start_date >= ?
			AND appointments.start_date < ?" . $locationSql . $employeeSql . "
			GROUP BY COALESCE(employees.id, 0), COALESCE(NULLIF(employees.name, ''), 'Unassigned')
			ORDER BY appointment_total DESC, employee_name ASC"
		);
		$stmt->bind_param('sss', $accountId, $fromDate, $untilDate);
		$stmt->execute();
		$result = $stmt->get_result();
		$totals = [];

		while ($row = $result->fetch_assoc()) {
			$totals[] = [
				'id' => (int) $row['employee_id'],
				'name' => (string) $row['employee_name'],
				'appointments' => (int) $row['appointment_total'],
			];
		}

		$stmt->close();

		return $totals;
	}

	#[\Override]
	public function topClientTotals(ReportScope $scope, int $year, int $limit): array
	{
		$accountId = $scope->accountId;
		[$fromDate, $untilDate] = $this->period($year, $year);
		$locationSql = $this->locationConstraint($scope, 'appointments.location_id');
		$employeeSql = $this->employeeConstraint($scope, 'appointments.employee_id');
		$stmt = $this->db()->prepare(
			'SELECT clients.id, clients.name, COUNT(*) AS appointment_total
			FROM Appointments AS appointments
			INNER JOIN Users AS clients
				ON clients.id = appointments.client_id
				AND clients.account_id = appointments.account_id
				AND clients.role_id = 1
			WHERE appointments.account_id = ?
			AND appointments.status = 1
			AND appointments.start_date >= ?
			AND appointments.start_date < ?' . $locationSql . $employeeSql . '
			GROUP BY clients.id, clients.name
			ORDER BY appointment_total DESC, clients.name ASC
			LIMIT ?'
		);
		$stmt->bind_param('sssi', $accountId, $fromDate, $untilDate, $limit);
		$stmt->execute();
		$result = $stmt->get_result();
		$totals = [];

		while ($row = $result->fetch_assoc()) {
			$totals[] = [
				'id' => (int) $row['id'],
				'name' => (string) $row['name'],
				'appointments' => (int) $row['appointment_total'],
			];
		}

		$stmt->close();

		return $totals;
	}

	#[\Override]
	public function availableLocations(ReportScope $scope): array
	{
		$accountId = $scope->accountId;
		$locationSql = $this->locationConstraint($scope, 'id');
		$stmt = $this->db()->prepare(
			'SELECT id, name
			FROM Business_Locations
			WHERE account_id = ?
			AND status = 1' . $locationSql . '
			ORDER BY name ASC'
		);
		$stmt->bind_param('s', $accountId);
		$stmt->execute();
		$result = $stmt->get_result();
		$locations = [];

		while ($row = $result->fetch_assoc()) {
			$locations[] = [
				'id' => (int) $row['id'],
				'name' => (string) $row['name'],
			];
		}

		$stmt->close();

		return $locations;
	}

	#[\Override]
	public function availableEmployees(ReportScope $scope): array
	{
		$accountId = $scope->accountId;
		$locationSql = $this->locationConstraint($scope, 'location_id');
		$employeeSql = $this->employeeConstraint($scope, 'id');
		$stmt = $this->db()->prepare(
			'SELECT id, name, location_id
			FROM Users
			WHERE account_id = ?
			AND role_id > 1
			AND status = 1' . $locationSql . $employeeSql . '
			ORDER BY name ASC'
		);
		$stmt->bind_param('s', $accountId);
		$stmt->execute();
		$result = $stmt->get_result();
		$employees = [];

		while ($row = $result->fetch_assoc()) {
			$employees[] = [
				'id' => (int) $row['id'],
				'name' => (string) $row['name'],
				'locationId' => (int) $row['location_id'],
			];
		}

		$stmt->close();

		return $employees;
	}

	#[\Override]
	public function availableYears(ReportScope $scope): array
	{
		$accountId = $scope->accountId;
		$appointmentLocationSql = $this->locationConstraint($scope, 'location_id');
		$appointmentEmployeeSql = $this->employeeConstraint($scope, 'employee_id');

		if ($scope->hasEmployeeFilter()) {
			$clientLocationSql = $this->locationConstraint($scope, 'appointments.location_id');
			$clientEmployeeSql = $this->employeeConstraint($scope, 'appointments.employee_id');
			$stmt = $this->db()->prepare(
				'SELECT report_year
				FROM (
					SELECT YEAR(start_date) AS report_year
					FROM Appointments
					WHERE account_id = ?' . $appointmentLocationSql . $appointmentEmployeeSql . '
					UNION
					SELECT YEAR(clients.date) AS report_year
					FROM Users AS clients
					INNER JOIN Appointments AS appointments
						ON appointments.client_id = clients.id
						AND appointments.account_id = clients.account_id
						AND appointments.status = 1
					WHERE clients.account_id = ?
					AND clients.role_id = 1' . $clientLocationSql . $clientEmployeeSql . '
				) AS stored_years
				WHERE report_year IS NOT NULL
				AND report_year BETWEEN 2000 AND YEAR(CURDATE()) + 1
				ORDER BY report_year DESC'
			);
			$stmt->bind_param('ss', $accountId, $accountId);
			$stmt->execute();
			$result = $stmt->get_result();
			$years = [];

			while ($row = $result->fetch_assoc()) {
				$years[] = (int) $row['report_year'];
			}

			$stmt->close();

			return $years;
		}

		$clientLocationSql = $this->locationConstraint($scope, 'location_id');
		$stmt = $this->db()->prepare(
			'SELECT report_year
			FROM (
				SELECT YEAR(start_date) AS report_year
				FROM Appointments
				WHERE account_id = ?' . $appointmentLocationSql . $appointmentEmployeeSql . '
				UNION
				SELECT YEAR(date) AS report_year
				FROM Users
				WHERE account_id = ?
				AND role_id = 1' . $clientLocationSql . '
			) AS stored_years
			WHERE report_year IS NOT NULL
			AND report_year BETWEEN 2000 AND YEAR(CURDATE()) + 1
			ORDER BY report_year DESC'
		);
		$stmt->bind_param('ss', $accountId, $accountId);
		$stmt->execute();
		$result = $stmt->get_result();
		$years = [];

		while ($row = $result->fetch_assoc()) {
			$years[] = (int) $row['report_year'];
		}

		$stmt->close();

		return $years;
	}

	private function groupedTotals(\mysqli_stmt $stmt): array
	{
		$stmt->execute();
		$result = $stmt->get_result();
		$totals = [];

		while ($row = $result->fetch_assoc()) {
			$totals[(int) $row['report_year']][(int) $row['report_month']] = (float) $row['total'];
		}

		$stmt->close();

		return $totals;
	}

	/** @return array{string, string} */
	private function period(int $fromYear, int $toYear): array
	{
		return [
			sprintf('%04d-01-01 00:00:00', $fromYear),
			sprintf('%04d-01-01 00:00:00', $toYear + 1),
		];
	}

	private function locationConstraint(ReportScope $scope, string $column): string
	{
		$locationIds = $scope->effectiveLocationIds();

		if ($locationIds === null) {
			return '';
		}

		if ($locationIds === []) {
			return ' AND 1 = 0';
		}

		return ' AND ' . $column . ' IN (' . implode(',', $locationIds) . ')';
	}

	private function employeeConstraint(ReportScope $scope, string $column): string
	{
		if (!$scope->hasEmployeeFilter()) {
			return '';
		}

		return ' AND ' . $column . ' = ' . $scope->employeeFilterId;
	}

	private function db(): mysqli
	{
		return $this->connection->mysqli();
	}
}
