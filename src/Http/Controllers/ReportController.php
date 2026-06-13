<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Http\Controllers;

use Fin\Narekaltro\Core\Request;
use Fin\Narekaltro\Core\Response;
use Fin\Narekaltro\Core\View;
use Fin\Narekaltro\Domain\Auth\Authorization;
use Fin\Narekaltro\Domain\Auth\Permission;
use Fin\Narekaltro\Domain\Reports\ReportAnalytics;
use Fin\Narekaltro\Domain\Reports\ReportFilters;
use Fin\Narekaltro\Domain\Reports\ReportMetric;

final class ReportController extends Controller
{
	public function __construct(
		View $view,
		private Authorization $auth,
		private ReportAnalytics $reports
	) {
		parent::__construct($view);
	}

	public function index(Request $request): Response
	{
		$user = $this->auth->require(Permission::REPORTS_VIEW);
		$report = $this->reports->overview($user, $this->year($request), $this->filters($request));

		return $this->render('reports.index', [
			'title' => 'Analytics & Reports',
			'report' => $report,
			'currentUser' => $user,
			'activeNav' => 'reports',
			'navigationAccess' => $this->navigationAccess($this->auth),
			'chartAssets' => true,
		]);
	}

	public function data(Request $request): Response
	{
		$user = $this->auth->require(Permission::REPORTS_VIEW);
		$metric = ReportMetric::tryFrom((string) $request->query('metric', 'appointments'));

		if ($metric === null) {
			return Response::json(['error' => 'Unknown report metric.'], 422);
		}

		if (!$this->reports->canViewMetric($user, $metric)) {
			return Response::json(['error' => 'Report metric is not available.'], 403);
		}

		return Response::json($this->reports->comparison(
			$user,
			$metric,
			$this->year($request),
			$this->filters($request)
		));
	}

	public function summary(Request $request): Response
	{
		$user = $this->auth->require(Permission::REPORTS_VIEW);
		$year = $this->year($request);

		return Response::json([
			'year' => $year,
			'summary' => $this->reports->summary($user, $year, null, $this->filters($request)),
		]);
	}

	public function insights(Request $request): Response
	{
		$user = $this->auth->require(Permission::REPORTS_VIEW);
		$year = $this->year($request);

		return Response::json($this->reports->insights($user, $year, $this->filters($request)));
	}

	private function year(Request $request): int
	{
		$currentYear = (int) date('Y');
		$year = (int) $request->query('year', $currentYear);

		return $year >= 2000 && $year <= $currentYear + 1 ? $year : $currentYear;
	}

	private function filters(Request $request): ReportFilters
	{
		return ReportFilters::fromArray([
			'location_id' => $request->query('location_id'),
			'employee_id' => $request->query('employee_id'),
		]);
	}
}
