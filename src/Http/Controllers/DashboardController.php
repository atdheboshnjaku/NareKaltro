<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Http\Controllers;

use Fin\Narekaltro\Core\Request;
use Fin\Narekaltro\Core\Response;
use Fin\Narekaltro\Core\View;
use Fin\Narekaltro\Domain\Auth\Authorization;
use Fin\Narekaltro\Domain\Auth\Permission;
use Fin\Narekaltro\Domain\Reports\ReportAnalytics;

final class DashboardController extends Controller
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
		$user = $this->auth->require(Permission::DASHBOARD_VIEW);

		return $this->render('dashboard.index', [
			'title' => 'Dashboard',
			'dashboard' => $this->reports->dashboard($user),
			'currentUser' => $user,
			'activeNav' => 'dashboard',
			'navigationAccess' => $this->navigationAccess($this->auth),
		]);
	}
}
