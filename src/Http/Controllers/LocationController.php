<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Http\Controllers;

use Fin\Narekaltro\Core\Request;
use Fin\Narekaltro\Core\Response;
use Fin\Narekaltro\Core\View;
use Fin\Narekaltro\Domain\Auth\AuthenticatedUser;
use Fin\Narekaltro\Domain\Auth\Authorization;
use Fin\Narekaltro\Domain\Auth\Permission;
use Fin\Narekaltro\Domain\Locations\BusinessLocation;
use Fin\Narekaltro\Domain\Locations\LocationFormData;
use Fin\Narekaltro\Domain\Locations\LocationInUse;
use Fin\Narekaltro\Domain\Locations\LocationManager;
use Fin\Narekaltro\Domain\Locations\LocationValidationFailed;

final class LocationController extends Controller
{
	public function __construct(
		View $view,
		private Authorization $auth,
		private LocationManager $locations
	) {
		parent::__construct($view);
	}

	public function index(Request $request): Response
	{
		$user = $this->auth->require(Permission::LOCATIONS_VIEW);

		return $this->renderIndex($request, $user);
	}

	public function create(Request $request): Response
	{
		$this->auth->require(Permission::LOCATIONS_MANAGE);

		return $this->renderCreate();
	}

	public function store(Request $request): Response
	{
		$user = $this->auth->require(Permission::LOCATIONS_MANAGE);
		$data = LocationFormData::fromArray($request->all());

		try {
			$this->locations->create($user->accountId, $data);
		} catch (LocationValidationFailed $exception) {
			return $this->renderCreate($data, $exception->errors(), 422);
		}

		return $this->redirect('/locations');
	}

	public function edit(Request $request): Response
	{
		$user = $this->auth->require(Permission::LOCATIONS_MANAGE);
		$location = $this->locations->find((int) $request->query('id'), $user->accountId);

		return $this->renderEdit($location);
	}

	public function update(Request $request): Response
	{
		$user = $this->auth->require(Permission::LOCATIONS_MANAGE);
		$locationId = (int) $request->input('id');
		$location = $this->locations->find($locationId, $user->accountId);
		$data = LocationFormData::fromArray($request->all());

		try {
			$this->locations->update($locationId, $user->accountId, $data);
		} catch (LocationValidationFailed $exception) {
			return $this->renderEdit($location, $data, $exception->errors(), 422);
		}

		return $this->redirect('/locations');
	}

	public function deactivate(Request $request): Response
	{
		$user = $this->auth->require(Permission::LOCATIONS_MANAGE);

		try {
			$this->locations->deactivate((int) $request->input('id'), $user->accountId);
		} catch (LocationInUse $exception) {
			return $this->renderIndex($request, $user, $exception->getMessage(), 422);
		}

		return $this->redirect('/locations');
	}

	private function renderIndex(
		Request $request,
		AuthenticatedUser $user,
		?string $removeError = null,
		int $status = 200
	): Response {
		$page = $this->pagination($request, 25, 100);
		$locations = $this->locations->page($user->accountId, $page);

		return $this->render('locations.index', [
			'title' => 'Locations',
			'locations' => $locations->items,
			'total' => $locations->total,
			'pagination' => $locations,
			'canManage' => $this->auth->can(Permission::LOCATIONS_MANAGE),
			'removeError' => $removeError,
			'currentUser' => $user,
			'activeNav' => 'locations',
			'navigationAccess' => $this->navigationAccess($this->auth),
		], $status);
	}

	private function renderCreate(
		?LocationFormData $old = null,
		array $errors = [],
		int $status = 200
	): Response {
		return $this->render('locations.create', [
			'title' => 'Create Location',
			'old' => $this->oldValues($old),
			'errors' => $errors,
			'currentUser' => $this->auth->user(),
			'activeNav' => 'locations',
			'navigationAccess' => $this->navigationAccess($this->auth),
		], $status);
	}

	private function renderEdit(
		BusinessLocation $location,
		?LocationFormData $old = null,
		array $errors = [],
		int $status = 200
	): Response {
		return $this->render('locations.edit', [
			'title' => 'Edit Location',
			'location' => $location,
			'old' => $this->oldValues($old, $location),
			'errors' => $errors,
			'currentUser' => $this->auth->user(),
			'activeNav' => 'locations',
			'navigationAccess' => $this->navigationAccess($this->auth),
		], $status);
	}

	private function oldValues(?LocationFormData $data, ?BusinessLocation $location = null): array
	{
		return ['name' => $data?->name ?? $location?->name ?? ''];
	}
}
