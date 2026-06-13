<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Http\Controllers;

use Fin\Narekaltro\Core\Request;
use Fin\Narekaltro\Core\Response;
use Fin\Narekaltro\Core\View;
use Fin\Narekaltro\Domain\Auth\AuthenticatedUser;
use Fin\Narekaltro\Domain\Auth\Authorization;
use Fin\Narekaltro\Domain\Auth\Permission;
use Fin\Narekaltro\Domain\Clients\ClientFormData;
use Fin\Narekaltro\Domain\Clients\ClientHistory;
use Fin\Narekaltro\Domain\Clients\ClientManager;
use Fin\Narekaltro\Domain\Clients\ClientProfile;
use Fin\Narekaltro\Domain\Clients\ClientValidationFailed;

final class ClientController extends Controller
{
	public function __construct(
		View $view,
		private Authorization $auth,
		private ClientManager $clients,
		private ClientHistory $history
	) {
		parent::__construct($view);
	}

	public function index(Request $request): Response
	{
		$user = $this->auth->require(Permission::CLIENTS_VIEW);
		$search = trim((string) $request->query('search', ''));
		$page = $this->pagination($request, 25, 100);
		$clients = $this->clients->page($user->accountId, $page, $search);

		return $this->render('clients.index', [
			'title' => 'Clients',
			'clients' => $clients->items,
			'total' => $clients->total,
			'pagination' => $clients,
			'search' => $search,
			'canCreate' => $this->auth->can(Permission::CLIENTS_CREATE),
			'canUpdate' => $this->auth->can(Permission::CLIENTS_UPDATE),
			'canDelete' => $this->auth->can(Permission::CLIENTS_DELETE),
			'currentUser' => $user,
			'activeNav' => 'clients',
			'navigationAccess' => $this->navigationAccess($this->auth),
		]);
	}

	public function create(Request $request): Response
	{
		$user = $this->auth->require(Permission::CLIENTS_CREATE);
		$defaults = new ClientFormData($user->locationId ?? 0, '', '', '', 0, 0, 0);

		return $this->renderCreate($user, $defaults);
	}

	public function store(Request $request): Response
	{
		$user = $this->auth->require(Permission::CLIENTS_CREATE);
		$data = ClientFormData::fromArray($request->all());

		try {
			$this->clients->create($user->accountId, $data);
		} catch (ClientValidationFailed $exception) {
			return $this->renderCreate($user, $data, $exception->errors(), 422);
		}

		return $this->redirect('/clients');
	}

	public function edit(Request $request): Response
	{
		$user = $this->auth->require(Permission::CLIENTS_UPDATE);
		$client = $this->clients->find((int) $request->query('id'), $user->accountId);

		return $this->renderEdit($user, $client, ClientFormData::fromClient($client));
	}

	public function update(Request $request): Response
	{
		$user = $this->auth->require(Permission::CLIENTS_UPDATE);
		$clientId = (int) $request->input('id');
		$client = $this->clients->find($clientId, $user->accountId);
		$data = ClientFormData::fromArray($request->all());

		try {
			$this->clients->update($clientId, $user->accountId, $data);
		} catch (ClientValidationFailed $exception) {
			return $this->renderEdit($user, $client, $data, $exception->errors(), 422);
		}

		return $this->redirect('/clients');
	}

	public function deactivate(Request $request): Response
	{
		$user = $this->auth->require(Permission::CLIENTS_DELETE);
		$this->clients->deactivate((int) $request->input('id'), $user->accountId);

		return $this->redirect('/clients');
	}

	public function history(Request $request): Response
	{
		$user = $this->auth->require(Permission::CLIENTS_VIEW);
		$client = $this->clients->find((int) $request->query('id'), $user->accountId);
		$page = $this->pagination($request, 15, 100);
		$entries = $this->history->pageForClient(
			$client->id,
			$user->accountId,
			$user,
			$page
		);

		return $this->render('clients.history', [
			'title' => 'Client History',
			'client' => $client,
			'entries' => $entries->items,
			'total' => $entries->total,
			'pagination' => $entries,
			'currentUser' => $user,
			'activeNav' => 'clients',
			'navigationAccess' => $this->navigationAccess($this->auth),
		]);
	}

	public function states(Request $request): Response
	{
		$this->auth->user();

		return Response::json(array_map(
			static fn ($option): array => $option->toArray(),
			$this->clients->states((int) $request->query('country_id'))
		));
	}

	public function cities(Request $request): Response
	{
		$this->auth->user();

		return Response::json(array_map(
			static fn ($option): array => $option->toArray(),
			$this->clients->cities(
				(int) $request->query('country_id'),
				(int) $request->query('state_id')
			)
		));
	}

	private function renderCreate(
		AuthenticatedUser $user,
		ClientFormData $old,
		array $errors = [],
		int $status = 200
	): Response {
		return $this->renderForm('clients.create', $user, null, $old, $errors, $status);
	}

	private function renderEdit(
		AuthenticatedUser $user,
		ClientProfile $client,
		ClientFormData $old,
		array $errors = [],
		int $status = 200
	): Response {
		return $this->renderForm('clients.edit', $user, $client, $old, $errors, $status);
	}

	private function renderForm(
		string $template,
		AuthenticatedUser $user,
		?ClientProfile $client,
		ClientFormData $old,
		array $errors,
		int $status
	): Response {
		return $this->render($template, [
			'title' => $client === null ? 'Create Client' : 'Edit Client',
			'client' => $client,
			'old' => $old,
			'locations' => $this->clients->activeLocations($user->accountId),
			'countries' => $this->clients->countries(),
			'states' => $this->clients->states($old->countryId),
			'cities' => $this->clients->cities($old->countryId, $old->stateId),
			'errors' => $errors,
			'currentUser' => $user,
			'activeNav' => 'clients',
			'navigationAccess' => $this->navigationAccess($this->auth),
		], $status);
	}
}
