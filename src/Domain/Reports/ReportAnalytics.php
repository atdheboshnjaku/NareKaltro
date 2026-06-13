<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Reports;

use Fin\Narekaltro\Domain\Appointments\AppointmentAccessControl;
use Fin\Narekaltro\Domain\Appointments\AppointmentCapability;
use Fin\Narekaltro\Domain\Appointments\AppointmentPolicyContext;
use Fin\Narekaltro\Domain\Appointments\AppointmentScope;
use Fin\Narekaltro\Domain\Auth\AuthenticatedUser;

final class ReportAnalytics
{
	private const MONTH_LABELS = [
		'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
		'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
	];

	public function __construct(
		private ReportRepository $reports,
		private ReportAccessControl $reportAccess,
		private AppointmentAccessControl $appointmentAccess
	) {
	}

	public function overview(AuthenticatedUser $viewer, int $year, ?ReportFilters $filters = null): array
	{
		$filters ??= ReportFilters::none();
		$scope = $this->scope($viewer, $filters);
		$appointments = $this->comparison($viewer, ReportMetric::Appointments, $year, $filters);

		return [
			'year' => $year,
			'comparisonYear' => $year - 1,
			'filters' => $filters->toArray(),
			'filterOptions' => $this->filterOptions($viewer, $filters),
			'metrics' => array_map(
				static fn (ReportMetric $metric): array => $metric->toArray(),
				array_values(array_filter(
					ReportMetric::cases(),
					static fn (ReportMetric $metric): bool => $scope->canViewValues
						|| $metric !== ReportMetric::BookedValue
				))
			),
			'years' => $this->years($scope, $year),
			'initial' => $appointments,
			'summary' => $this->summary($viewer, $year, $appointments, $filters),
			'insights' => $this->insights($viewer, $year, $filters),
		];
	}

	public function summary(
		AuthenticatedUser $viewer,
		int $year,
		?array $appointments = null,
		?ReportFilters $filters = null
	): array {
		$filters ??= ReportFilters::none();
		$appointments ??= $this->comparison($viewer, ReportMetric::Appointments, $year, $filters);
		$cancellations = $this->comparison($viewer, ReportMetric::Cancellations, $year, $filters);
		$clients = $this->comparison($viewer, ReportMetric::NewClients, $year, $filters);
		$scope = $this->scope($viewer, $filters);

		return [
			$this->summaryItem(ReportMetric::Appointments, $appointments),
			$this->summaryItem(ReportMetric::Cancellations, $cancellations),
			$this->summaryItem(ReportMetric::NewClients, $clients),
			[
				'id' => 'active_clients',
				'label' => 'Active clients',
				'icon' => 'fa-users',
				'format' => 'number',
				'value' => $this->reports->activeClientCount($scope),
				'difference' => null,
				'percent' => null,
			],
		];
	}

	public function insights(AuthenticatedUser $viewer, int $year, ?ReportFilters $filters = null): array
	{
		$filters ??= ReportFilters::none();
		return $this->insightsForScope($viewer, $this->scope($viewer, $filters), $year);
	}

	public function dashboard(AuthenticatedUser $viewer): array
	{
		$year = (int) date('Y');
		$today = date('Y-m-d 00:00:00');
		$tomorrow = date('Y-m-d 00:00:00', strtotime('+1 day'));
		$reportScope = $this->reportAccess->scopeFor($viewer);
		$scope = $this->dashboardScope($viewer, $reportScope->canViewValues);
		$appointments = $this->comparisonForScope($viewer, ReportMetric::Appointments, $scope, $year);
		$insights = $this->insightsForScope($viewer, $scope, $year);
		$stats = [
			[
				'label' => "Today's appointments",
				'icon' => 'fa-calendar-check-o',
				'format' => 'number',
				'value' => $this->reports->activeAppointmentCountBetween(
					$scope,
					$today,
					$tomorrow
				),
				'detail' => 'Scheduled today',
			],
			[
				'label' => 'Upcoming appointments',
				'icon' => 'fa-calendar',
				'format' => 'number',
				'value' => $this->reports->upcomingAppointmentCount($scope, $today),
				'detail' => 'From today onward',
			],
			[
				'label' => 'Active clients',
				'icon' => 'fa-users',
				'format' => 'number',
				'value' => $this->reports->activeClientCount($scope),
				'detail' => 'Available for booking',
			],
		];

		if ($insights['canViewValues']) {
			$stats[] = [
				'label' => 'Visible booked value',
				'icon' => 'fa-eur',
				'format' => 'currency',
				'value' => $insights['visibleValue'],
				'detail' => $year . ' active bookings',
			];
		}

		return [
			'year' => $year,
			'dateLabel' => date('l, d F Y'),
			'appointments' => $appointments['current']['total'],
			'insights' => $insights,
			'stats' => $stats,
		];
	}

	private function insightsForScope(AuthenticatedUser $viewer, ReportScope $scope, int $year): array
	{
		$serviceValues = [];
		$locationValues = [];
		$employeeValues = [];
		$visibleValue = 0.0;

		if ($scope->canViewValues) {
			foreach ($this->reports->activeCostEntries($scope, $year, $year) as $entry) {
				$context = new AppointmentPolicyContext(
					appointmentId: $entry->appointmentId,
					locationId: $entry->locationId,
					serviceId: $entry->serviceId
				);

				if (!$this->appointmentAccess->can($viewer, AppointmentCapability::CostView, $context)) {
					continue;
				}

				$value = (float) $entry->value;
				$visibleValue += $value;
				$serviceValues[$entry->serviceId] = ($serviceValues[$entry->serviceId] ?? 0.0) + $value;
				$locationValues[$entry->locationId] = ($locationValues[$entry->locationId] ?? 0.0) + $value;
				$employeeId = $entry->employeeId ?? 0;
				$employeeValues[$employeeId] = ($employeeValues[$employeeId] ?? 0.0) + $value;
			}
		}

		return [
			'year' => $year,
			'canViewValues' => $scope->canViewValues,
			'visibleValue' => round($visibleValue, 2),
			'services' => array_map(
				static fn (array $service): array => $service + [
					'visibleValue' => round($serviceValues[$service['id']] ?? 0.0, 2),
				],
				$this->reports->serviceDemandTotals($scope, $year)
			),
			'locations' => array_map(
				static fn (array $location): array => $location + [
					'visibleValue' => round($locationValues[$location['id']] ?? 0.0, 2),
				],
				$this->reports->locationDemandTotals($scope, $year)
			),
			'employees' => array_map(
				static fn (array $employee): array => $employee + [
					'visibleValue' => round($employeeValues[$employee['id']] ?? 0.0, 2),
				],
				$this->reports->employeeDemandTotals($scope, $year)
			),
			'clients' => $this->reports->topClientTotals($scope, $year, 8),
		];
	}

	public function comparison(
		AuthenticatedUser $viewer,
		ReportMetric $metric,
		int $year,
		?ReportFilters $filters = null
	): array
	{
		$filters ??= ReportFilters::none();
		return $this->comparisonForScope($viewer, $metric, $this->scope($viewer, $filters), $year);
	}

	private function comparisonForScope(
		AuthenticatedUser $viewer,
		ReportMetric $metric,
		ReportScope $scope,
		int $year
	): array {
		$fromYear = $year - 1;
		$totals = match ($metric) {
			ReportMetric::Appointments => $this->reports->appointmentMonthlyTotals(
				$scope,
				$fromYear,
				$year,
				false
			),
			ReportMetric::Cancellations => $this->reports->appointmentMonthlyTotals(
				$scope,
				$fromYear,
				$year,
				true
			),
			ReportMetric::NewClients => $this->reports->newClientMonthlyTotals(
				$scope,
				$fromYear,
				$year
			),
			ReportMetric::BookedValue => $this->visibleBookedValueTotals($viewer, $scope, $fromYear, $year),
		};

		$current = MonthlyReportSeries::fromTotals($year, $totals[$year] ?? []);
		$previous = MonthlyReportSeries::fromTotals($fromYear, $totals[$fromYear] ?? []);
		$difference = round($current->total() - $previous->total(), 2);
		$percent = $previous->total() === 0.0
			? null
			: round(($difference / $previous->total()) * 100, 1);

		return [
			'metric' => $metric->toArray(),
			'year' => $year,
			'comparisonYear' => $fromYear,
			'labels' => self::MONTH_LABELS,
			'current' => $current->toArray(),
			'previous' => $previous->toArray(),
			'difference' => $difference,
			'percent' => $percent,
		];
	}

	public function canViewMetric(AuthenticatedUser $viewer, ReportMetric $metric): bool
	{
		return $metric !== ReportMetric::BookedValue
			|| $this->reportAccess->scopeFor($viewer)->canViewValues;
	}

	public function filterOptions(AuthenticatedUser $viewer, ?ReportFilters $filters = null): array
	{
		$filters ??= ReportFilters::none();
		$scope = $this->reportAccess->scopeFor($viewer);
		$employeeScope = $scope->withFilters(new ReportFilters(locationId: $filters->locationId));

		return [
			'locations' => $this->reports->availableLocations($scope),
			'employees' => $this->reports->availableEmployees($employeeScope),
		];
	}

	private function scope(AuthenticatedUser $viewer, ReportFilters $filters): ReportScope
	{
		return $this->reportAccess->scopeFor($viewer)->withFilters($filters);
	}

	private function dashboardScope(AuthenticatedUser $viewer, bool $canViewValues): ReportScope
	{
		$scope = $this->appointmentAccess->scopeFor($viewer);
		$reportScope = $scope->isAccountWide()
			? ReportScope::account($scope->accountId, $canViewValues)
			: ReportScope::locations($scope->accountId, $scope->locationIds ?? [], $canViewValues);

		if ($scope->employeeId === null) {
			return $reportScope;
		}

		return $reportScope->withFilters(new ReportFilters(employeeId: $scope->employeeId));
	}

	private function summaryItem(ReportMetric $metric, array $comparison): array
	{
		return [
			'id' => $metric->value,
			'label' => $metric->label(),
			'icon' => $metric->icon(),
			'format' => $metric->format(),
			'value' => $comparison['current']['total'],
			'difference' => $comparison['difference'],
			'percent' => $comparison['percent'],
		];
	}

	/** @return array<int, array<int, float>> */
	private function visibleBookedValueTotals(
		AuthenticatedUser $viewer,
		ReportScope $scope,
		int $fromYear,
		int $toYear
	): array {
		$totals = [];

		if (!$scope->canViewValues) {
			return $totals;
		}

		foreach ($this->reports->activeCostEntries($scope, $fromYear, $toYear) as $entry) {
			$context = new AppointmentPolicyContext(
				appointmentId: $entry->appointmentId,
				locationId: $entry->locationId,
				serviceId: $entry->serviceId
			);

			if (!$this->appointmentAccess->can($viewer, AppointmentCapability::CostView, $context)) {
				continue;
			}

			$totals[$entry->year][$entry->month] = round(
				($totals[$entry->year][$entry->month] ?? 0.0) + (float) $entry->value,
				2
			);
		}

		return $totals;
	}

	/** @return list<int> */
	private function years(ReportScope $scope, int $selectedYear): array
	{
		$years = array_values(array_unique(array_merge(
			[$selectedYear, $selectedYear - 1],
			$this->reports->availableYears($scope)
		)));
		rsort($years);

		return $years;
	}
}
