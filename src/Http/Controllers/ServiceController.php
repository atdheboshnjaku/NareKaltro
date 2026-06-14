<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Http\Controllers;

use Fin\Narekaltro\Core\Request;
use Fin\Narekaltro\Core\Response;
use Fin\Narekaltro\Core\View;
use Fin\Narekaltro\Domain\Auth\Authorization;
use Fin\Narekaltro\Domain\Auth\Permission;
use Fin\Narekaltro\Domain\Services\ServiceFormData;
use Fin\Narekaltro\Domain\Services\ServiceManager;
use Fin\Narekaltro\Domain\Services\ServiceOffering;
use Fin\Narekaltro\Domain\Services\ServiceValidationFailed;

final class ServiceController extends Controller
{
	public function __construct(
		View $view,
		private Authorization $auth,
		private ServiceManager $services
	) {
		parent::__construct($view);
	}

	public function index(Request $request): Response
	{
		$user = $this->auth->require(Permission::SERVICES_VIEW);
		$page = $this->pagination($request, 25, 100);
		$services = $this->services->page($user->accountId, $page);

		return $this->render('services.index', [
			'title' => 'Services',
			'services' => $services->items,
			'total' => $services->total,
			'pagination' => $services,
			'canManage' => $this->auth->can(Permission::SERVICES_MANAGE),
			'currentUser' => $user,
			'activeNav' => 'services',
			'navigationAccess' => $this->navigationAccess($this->auth),
		]);
	}

	public function create(Request $request): Response
	{
		$this->auth->require(Permission::SERVICES_MANAGE);

		return $this->renderCreate();
	}

	public function store(Request $request): Response
	{
		$user = $this->auth->require(Permission::SERVICES_MANAGE);
		$data = ServiceFormData::fromArray($request->all());

		try {
			$this->services->create($user->accountId, $data);
		} catch (ServiceValidationFailed $exception) {
			return $this->renderCreate($data, $exception->errors(), 422);
		}

		return $this->redirect('/services');
	}

	public function edit(Request $request): Response
	{
		$user = $this->auth->require(Permission::SERVICES_MANAGE);
		$service = $this->services->find((int) $request->query('id'), $user->accountId);

		return $this->renderEdit($service);
	}

	public function update(Request $request): Response
	{
		$user = $this->auth->require(Permission::SERVICES_MANAGE);
		$serviceId = (int) $request->input('id');
		$service = $this->services->find($serviceId, $user->accountId);
		$data = ServiceFormData::fromArray($request->all());

		try {
			$this->services->update($serviceId, $user->accountId, $data);
		} catch (ServiceValidationFailed $exception) {
			return $this->renderEdit($service, $data, $exception->errors(), 422);
		}

		return $this->redirect('/services');
	}

	public function deactivate(Request $request): Response
	{
		$user = $this->auth->require(Permission::SERVICES_MANAGE);
		$this->services->deactivate((int) $request->input('id'), $user->accountId);

		return $this->redirect('/services');
	}

	private function renderCreate(
		?ServiceFormData $old = null,
		array $errors = [],
		int $status = 200
	): Response {
		return $this->render('services.create', [
			'title' => 'Create Service',
			'old' => $this->oldValues($old),
			'errors' => $errors,
			'currentUser' => $this->auth->user(),
			'activeNav' => 'services',
			'navigationAccess' => $this->navigationAccess($this->auth),
		], $status);
	}

	private function renderEdit(
		ServiceOffering $service,
		?ServiceFormData $old = null,
		array $errors = [],
		int $status = 200
	): Response {
		return $this->render('services.edit', [
			'title' => 'Edit Service',
			'service' => $service,
			'old' => $this->oldValues($old, $service),
			'errors' => $errors,
			'currentUser' => $this->auth->user(),
			'activeNav' => 'services',
			'navigationAccess' => $this->navigationAccess($this->auth),
		], $status);
	}

	private function oldValues(?ServiceFormData $data, ?ServiceOffering $service = null): array
	{
		return [
			'name' => $data?->name ?? $service?->name ?? '',
			'background' => $data?->background ?? $service?->background ?? '#f1faff',
			'color' => $data?->color ?? $service?->color ?? '#009ef7',
			'quote_only' => $data?->quoteOnly ?? $service?->quoteOnly ?? false,
		];
	}
}
