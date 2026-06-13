<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Http\Controllers;

use DateTimeImmutable;
use Exception;
use Fin\Narekaltro\Core\Request;
use Fin\Narekaltro\Core\Response;
use Fin\Narekaltro\Core\View;
use Fin\Narekaltro\Domain\Appointments\AppointmentCalendar;
use Fin\Narekaltro\Domain\Appointments\AppointmentFormData;
use Fin\Narekaltro\Domain\Appointments\AppointmentManager;
use Fin\Narekaltro\Domain\Appointments\AppointmentValidationFailed;
use Fin\Narekaltro\Domain\Auth\Authorization;
use Fin\Narekaltro\Domain\Auth\Permission;
use Fin\Narekaltro\Domain\Clients\ClientFormData;
use Fin\Narekaltro\Domain\Clients\ClientManager;
use Fin\Narekaltro\Domain\Clients\ClientValidationFailed;

final class AppointmentController extends Controller
{
	public function __construct(
		View $view,
		private Authorization $auth,
		private AppointmentCalendar $calendar,
		private AppointmentManager $appointments,
		private ClientManager $clients
	) {
		parent::__construct($view);
	}

	public function index(Request $request): Response
	{
		$user = $this->auth->require(Permission::APPOINTMENTS_VIEW);
		$options = $this->appointments->formOptions($user);
		$canCreate = $this->auth->can(Permission::APPOINTMENTS_CREATE);
		$canCreateClient = $canCreate && $this->auth->can(Permission::CLIENTS_CREATE);

		return $this->render('appointments.index', [
			'title' => 'Appointments',
			'upcomingCount' => $this->calendar->upcomingCountFor($user),
			'canManageSettings' => $this->auth->can(Permission::APPOINTMENTS_SETTINGS_MANAGE),
			'canViewClientHistory' => $this->auth->can(Permission::CLIENTS_VIEW),
			'canCreate' => $canCreate,
			'canUpdate' => $this->auth->can(Permission::APPOINTMENTS_UPDATE),
			'canDelete' => $this->auth->can(Permission::APPOINTMENTS_DELETE),
			'canCreateClient' => $canCreateClient,
			'locations' => $options['locations'],
			'employees' => $options['employees'],
			'services' => $options['services'],
			'countries' => $canCreateClient ? $this->clients->countries() : [],
			'fullCalendarAssets' => true,
			'currentUser' => $user,
			'activeNav' => 'appointments',
			'navigationAccess' => $this->navigationAccess($this->auth),
		]);
	}

	public function feed(Request $request): Response
	{
		$user = $this->auth->requireRole('role_admin');

		return Response::json($this->calendar->events(
			$user,
			$this->date($request->query('start')),
			$this->date($request->query('end'))
		));
	}

	public function events(Request $request): Response
	{
		$user = $this->auth->require(Permission::APPOINTMENTS_VIEW);

		return Response::json($this->calendar->events(
			$user,
			$this->date($request->input('start')),
			$this->date($request->input('end'))
		));
	}

	public function clientHistory(Request $request): Response
	{
		$user = $this->auth->require(Permission::APPOINTMENTS_VIEW);
		$this->auth->require(Permission::CLIENTS_VIEW);

		return Response::json($this->calendar->recentClientHistory(
			$user,
			(int) $request->query('client_id')
		));
	}

	public function clientSearch(Request $request): Response
	{
		$user = $this->auth->require(Permission::APPOINTMENTS_CREATE);
		$search = trim((string) ($request->query('term') ?? $request->query('q', '')));
		$clients = $this->clients->page($user->accountId, $this->pagination($request, 20, 50), $search);

		return Response::json([
			'results' => array_map(
				static fn ($client): array => [
					'id' => $client->id,
					'text' => $client->name,
					'email' => $client->email,
					'location' => $client->locationName,
				],
				$clients->items
			),
			'pagination' => [
				'more' => $clients->hasNext(),
			],
		]);
	}

	public function capabilities(Request $request): Response
	{
		$user = $this->auth->require(Permission::APPOINTMENTS_VIEW);
		$serviceIds = $request->query('service_ids', []);
		$serviceIds = is_array($serviceIds) ? array_values(array_unique(array_map('intval', $serviceIds))) : [];
		$appointmentId = (int) $request->query('appointment_id', 0);

		try {
			return Response::json($this->appointments->capabilities(
				$user,
				(int) $request->query('location_id', 0),
				array_values(array_filter($serviceIds, static fn (int $id): bool => $id > 0)),
				$appointmentId > 0 ? $appointmentId : null
			));
		} catch (AppointmentValidationFailed $exception) {
			return Response::json(['errors' => $exception->errors()], 422);
		}
	}

	public function store(Request $request): Response
	{
		$user = $this->auth->require(Permission::APPOINTMENTS_CREATE);

		try {
			$id = $this->appointments->create($user, AppointmentFormData::fromArray($request->all()));

			return Response::json(['id' => $id], 201);
		} catch (AppointmentValidationFailed $exception) {
			return Response::json(['errors' => $exception->errors()], 422);
		}
	}

	public function update(Request $request): Response
	{
		$user = $this->auth->require(Permission::APPOINTMENTS_UPDATE);
		$appointmentId = (int) $request->input('id', 0);

		try {
			$this->appointments->update(
				$user,
				$appointmentId,
				AppointmentFormData::fromArray($request->all(), (int) $request->input('client_id', 0))
			);

			return Response::json(['updated' => true]);
		} catch (AppointmentValidationFailed $exception) {
			return Response::json(['errors' => $exception->errors()], 422);
		}
	}

	public function reschedule(Request $request): Response
	{
		$user = $this->auth->require(Permission::APPOINTMENTS_UPDATE);

		try {
			$this->appointments->reschedule(
				$user,
				(int) $request->input('id', 0),
				(string) $request->input('start_date', ''),
				$request->input('end_date') === null ? null : (string) $request->input('end_date')
			);

			return Response::json(['updated' => true]);
		} catch (AppointmentValidationFailed $exception) {
			return Response::json(['errors' => $exception->errors()], 422);
		}
	}

	public function cancel(Request $request): Response
	{
		$user = $this->auth->require(Permission::APPOINTMENTS_DELETE);
		$this->appointments->cancel($user, (int) $request->input('id', 0));

		return Response::json(['cancelled' => true]);
	}

	public function storeClient(Request $request): Response
	{
		$user = $this->auth->require(Permission::CLIENTS_CREATE);
		$this->auth->require(Permission::APPOINTMENTS_CREATE);
		$data = ClientFormData::fromArray($request->all());

		try {
			$id = $this->clients->create($user->accountId, $data);

			return Response::json(['id' => $id, 'name' => $data->name], 201);
		} catch (ClientValidationFailed $exception) {
			return Response::json(['errors' => $exception->errors()], 422);
		}
	}

	private function date(mixed $date): ?DateTimeImmutable
	{
		if (!is_string($date) || trim($date) === '') {
			return null;
		}

		try {
			return new DateTimeImmutable($date);
		} catch (Exception) {
			return null;
		}
	}
}
