<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Http\Controllers;

use Fin\Narekaltro\Core\Request;
use Fin\Narekaltro\Core\Response;
use Fin\Narekaltro\Core\View;
use Fin\Narekaltro\Domain\Auth\Authorization;
use Fin\Narekaltro\Domain\Auth\Permission;
use Fin\Narekaltro\Domain\Shared\PageRequest;

abstract class Controller
{
	public function __construct(protected View $view)
	{
	}

	protected function render(string $template, array $data = [], int $status = 200): Response
	{
		return Response::html($this->view->render($template, $data), $status);
	}

	protected function redirect(string $to): Response
	{
		return Response::redirect($to);
	}

	protected function pagination(Request $request, int $defaultPerPage = 25, int $maxPerPage = 100): PageRequest
	{
		return PageRequest::fromArray($request->all(), $defaultPerPage, $maxPerPage);
	}

	protected function navigationAccess(Authorization $auth): array
	{
		return [
			'dashboard' => $auth->can(Permission::DASHBOARD_VIEW),
			'appointments' => $auth->can(Permission::APPOINTMENTS_VIEW),
			'locations' => $auth->can(Permission::LOCATIONS_VIEW),
			'services' => $auth->can(Permission::SERVICES_VIEW),
			'users' => $auth->can(Permission::USERS_VIEW),
			'roles' => $auth->can(Permission::ROLES_VIEW),
			'clients' => $auth->can(Permission::CLIENTS_VIEW),
			'reports' => $auth->can(Permission::REPORTS_VIEW),
		];
	}
}
