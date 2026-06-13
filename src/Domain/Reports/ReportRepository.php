<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Reports;

interface ReportRepository
{
	/** @return array<int, array<int, int|float>> */
	public function appointmentMonthlyTotals(
		ReportScope $scope,
		int $fromYear,
		int $toYear,
		bool $cancelled
	): array;

	/** @return array<int, array<int, int|float>> */
	public function newClientMonthlyTotals(ReportScope $scope, int $fromYear, int $toYear): array;

	/** @return list<ReportCostEntry> */
	public function activeCostEntries(ReportScope $scope, int $fromYear, int $toYear): array;

	public function activeClientCount(ReportScope $scope): int;

	public function activeAppointmentCountBetween(ReportScope $scope, string $fromDate, string $untilDate): int;

	public function upcomingAppointmentCount(ReportScope $scope, string $fromDate): int;

	/** @return list<array{id: int, name: string, background: string, color: string, appointments: int}> */
	public function serviceDemandTotals(ReportScope $scope, int $year): array;

	/** @return list<array{id: int, name: string, appointments: int}> */
	public function locationDemandTotals(ReportScope $scope, int $year): array;

	/** @return list<array{id: int, name: string, appointments: int}> */
	public function employeeDemandTotals(ReportScope $scope, int $year): array;

	/** @return list<array{id: int, name: string, appointments: int}> */
	public function topClientTotals(ReportScope $scope, int $year, int $limit): array;

	/** @return list<array{id: int, name: string}> */
	public function availableLocations(ReportScope $scope): array;

	/** @return list<array{id: int, name: string, locationId: int}> */
	public function availableEmployees(ReportScope $scope): array;

	/** @return list<int> */
	public function availableYears(ReportScope $scope): array;
}
