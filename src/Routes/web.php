<?php

declare(strict_types=1);

use Fin\Narekaltro\Core\Request;
use Fin\Narekaltro\Core\Response;
use Fin\Narekaltro\Core\Router;
use Fin\Narekaltro\Http\Controllers\AuthController;
use Fin\Narekaltro\Http\Controllers\ClientController;
use Fin\Narekaltro\Http\Controllers\DashboardController;
use Fin\Narekaltro\Http\Controllers\LocationController;
use Fin\Narekaltro\Http\Controllers\AppointmentSettingsController;
use Fin\Narekaltro\Http\Controllers\AppointmentController;
use Fin\Narekaltro\Http\Controllers\RoleController;
use Fin\Narekaltro\Http\Controllers\ReportController;
use Fin\Narekaltro\Http\Controllers\ServiceController;
use Fin\Narekaltro\Http\Controllers\StaffAccessController;
use Fin\Narekaltro\Http\Controllers\StaffController;

return static function (Router $router): void {
	$route = static function (string $method, string $path, array|\Closure $handler) use ($router): void {
		$router->add($method, $path, $handler);
	};

	$route('GET', '/', [DashboardController::class, 'index']);
	$router->get('/index', [AuthController::class, 'index']);
	$router->get('/login', [AuthController::class, 'login']);
	$router->post('/login', [AuthController::class, 'authenticate']);
	$router->get('/forgot-password', [AuthController::class, 'forgotPassword']);
	$router->post('/forgot-password', [AuthController::class, 'sendPasswordReset']);
	$router->get('/reset-password', [AuthController::class, 'resetPassword']);
	$router->post('/reset-password', [AuthController::class, 'completePasswordReset']);
	$router->get('/register', [AuthController::class, 'register']);
	$router->post('/register', [AuthController::class, 'storeRegistration']);
	$router->get('/verify', [AuthController::class, 'verify']);
	$router->post('/verify', [AuthController::class, 'completeVerification']);
	$router->get('/logout', static fn (): Response => Response::redirect('/'));
	$router->post('/logout', [AuthController::class, 'logout']);

	$route('GET', '/roles', [RoleController::class, 'index']);
	$route('GET', '/roles/create', [RoleController::class, 'create']);
	$route('POST', '/roles/store', [RoleController::class, 'store']);
	$route('GET', '/roles/edit', [RoleController::class, 'edit']);
	$route('POST', '/roles/update', [RoleController::class, 'update']);

	$route('GET', '/appointments', [AppointmentController::class, 'index']);
	$route('GET', '/appointments/feed', [AppointmentController::class, 'feed']);
	$route('POST', '/appointments/events', [AppointmentController::class, 'events']);
	$route('GET', '/appointments/client-history', [AppointmentController::class, 'clientHistory']);
	$route('GET', '/appointments/clients/search', [AppointmentController::class, 'clientSearch']);
	$route('GET', '/appointments/capabilities', [AppointmentController::class, 'capabilities']);
	$route('POST', '/appointments/store', [AppointmentController::class, 'store']);
	$route('POST', '/appointments/update', [AppointmentController::class, 'update']);
	$route('POST', '/appointments/reschedule', [AppointmentController::class, 'reschedule']);
	$route('POST', '/appointments/cancel', [AppointmentController::class, 'cancel']);
	$route('POST', '/appointments/clients/store', [AppointmentController::class, 'storeClient']);
	$route('GET', '/appointments/settings', [AppointmentSettingsController::class, 'index']);
	$route('POST', '/appointments/settings/defaults', [AppointmentSettingsController::class, 'updateDefaults']);
	$route('GET', '/appointments/settings/rules/create', [AppointmentSettingsController::class, 'create']);
	$route('POST', '/appointments/settings/rules/store', [AppointmentSettingsController::class, 'store']);
	$route('GET', '/appointments/settings/rules/edit', [AppointmentSettingsController::class, 'edit']);
	$route('POST', '/appointments/settings/rules/update', [AppointmentSettingsController::class, 'update']);
	$route('POST', '/appointments/settings/rules/deactivate', [AppointmentSettingsController::class, 'deactivate']);

	$route('GET', '/services', [ServiceController::class, 'index']);
	$route('GET', '/services/create', [ServiceController::class, 'create']);
	$route('POST', '/services/store', [ServiceController::class, 'store']);
	$route('GET', '/services/edit', [ServiceController::class, 'edit']);
	$route('POST', '/services/update', [ServiceController::class, 'update']);
	$route('POST', '/services/deactivate', [ServiceController::class, 'deactivate']);

	$route('GET', '/locations', [LocationController::class, 'index']);
	$route('GET', '/locations/create', [LocationController::class, 'create']);
	$route('POST', '/locations/store', [LocationController::class, 'store']);
	$route('GET', '/locations/edit', [LocationController::class, 'edit']);
	$route('POST', '/locations/update', [LocationController::class, 'update']);
	$route('POST', '/locations/deactivate', [LocationController::class, 'deactivate']);

	$route('GET', '/users', [StaffController::class, 'index']);
	$route('GET', '/users/create', [StaffController::class, 'create']);
	$route('POST', '/users/store', [StaffController::class, 'store']);
	$route('GET', '/users/edit', [StaffController::class, 'edit']);
	$route('POST', '/users/update', [StaffController::class, 'update']);
	$route('POST', '/users/deactivate', [StaffController::class, 'deactivate']);
	$route('GET', '/users/access', [StaffAccessController::class, 'edit']);
	$route('POST', '/users/access/update', [StaffAccessController::class, 'update']);
	$route('POST', '/users/access/reset', [StaffAccessController::class, 'reset']);

	$route('GET', '/clients', [ClientController::class, 'index']);
	$route('GET', '/clients/create', [ClientController::class, 'create']);
	$route('POST', '/clients/store', [ClientController::class, 'store']);
	$route('GET', '/clients/edit', [ClientController::class, 'edit']);
	$route('POST', '/clients/update', [ClientController::class, 'update']);
	$route('POST', '/clients/deactivate', [ClientController::class, 'deactivate']);
	$route('GET', '/clients/history', [ClientController::class, 'history']);
	$route('GET', '/clients/geography/states', [ClientController::class, 'states']);
	$route('GET', '/clients/geography/cities', [ClientController::class, 'cities']);

	$route('GET', '/reports', [ReportController::class, 'index']);
	$route('GET', '/reports/data', [ReportController::class, 'data']);
	$route('GET', '/reports/summary', [ReportController::class, 'summary']);
	$route('GET', '/reports/insights', [ReportController::class, 'insights']);

	$route('GET', '/health', static function (Request $request): Response {
		return Response::json([
			'status' => 'ok',
			'app' => 'narekaltro-mvc',
		]);
	});
};
