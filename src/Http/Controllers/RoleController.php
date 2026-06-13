<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Http\Controllers;

use Fin\Narekaltro\Core\Request;
use Fin\Narekaltro\Core\Response;
use Fin\Narekaltro\Core\View;
use Fin\Narekaltro\Domain\Auth\Authorization;
use Fin\Narekaltro\Domain\Auth\Permission;
use Fin\Narekaltro\Domain\Auth\RoleFormData;
use Fin\Narekaltro\Domain\Auth\RoleManager;
use Fin\Narekaltro\Domain\Auth\RoleValidationFailed;

final class RoleController extends Controller
{
	public function __construct(
		View $view,
		private Authorization $auth,
		private RoleManager $roles
	) {
		parent::__construct($view);
	}

	public function index(Request $request): Response
	{
		$user = $this->auth->require(Permission::ROLES_VIEW);
		$page = $this->pagination($request, 25, 100);
		$roles = $this->roles->rolesPage($user->accountId, $page);

		return $this->render('roles.index', [
			'title' => 'Roles',
			'roles' => $roles->items,
			'total' => $roles->total,
			'pagination' => $roles,
			'canManage' => $this->auth->can(Permission::ROLES_MANAGE),
			'currentUser' => $user,
			'activeNav' => 'roles',
			'navigationAccess' => $this->navigationAccess($this->auth),
		]);
	}

	public function create(Request $request): Response
	{
		$user = $this->auth->require(Permission::ROLES_MANAGE);

		return $this->renderCreate(accountId: $user->accountId);
	}

	public function store(Request $request): Response
	{
		$user = $this->auth->require(Permission::ROLES_MANAGE);
		$data = RoleFormData::fromArray($request->all());

		try {
			$roleId = $this->roles->create($user->accountId, $user->id, $data);
		} catch (RoleValidationFailed $exception) {
			return $this->renderCreate($user->accountId, $data, $exception->errors(), 422);
		}

		return $this->redirect('/roles/edit?id=' . rawurlencode($roleId));
	}

	public function edit(Request $request): Response
	{
		$user = $this->auth->require(Permission::ROLES_MANAGE);
		$roleId = (string) $request->query('id');

		return $this->renderEdit($user->accountId, $this->roles->find($user->accountId, $roleId));
	}

	public function update(Request $request): Response
	{
		$user = $this->auth->require(Permission::ROLES_MANAGE);
		$roleId = (string) $request->input('role_id');
		$role = $this->roles->find($user->accountId, $roleId);
		$data = RoleFormData::fromArray($request->all());

		try {
			$this->roles->update($user->accountId, $user->id, $roleId, $data);
		} catch (RoleValidationFailed $exception) {
			return $this->renderEdit($user->accountId, $role, $data, $exception->errors(), 422);
		}

		return $this->redirect('/roles/edit?id=' . rawurlencode($roleId));
	}

	private function renderCreate(
		string $accountId,
		?RoleFormData $old = null,
		array $errors = [],
		int $status = 200
	): Response {
		return $this->render('roles.create', [
			'title' => 'Create Role',
			'permissions' => $this->roles->permissionGroups($accountId),
			'old' => $this->oldValues($old),
			'selectedPermissions' => $old?->permissions ?? [],
			'errors' => $errors,
			'currentUser' => $this->auth->user(),
			'activeNav' => 'roles',
			'navigationAccess' => $this->navigationAccess($this->auth),
		], $status);
	}

	private function renderEdit(
		string $accountId,
		array $role,
		?RoleFormData $old = null,
		array $errors = [],
		int $status = 200
	): Response {
		return $this->render('roles.edit', [
			'title' => 'Edit Role',
			'role' => $role,
			'permissions' => $this->roles->permissionGroups($accountId),
			'old' => $this->oldValues($old, $role),
			'selectedPermissions' => $old?->permissions ?? $role['permissions'],
			'errors' => $errors,
			'currentUser' => $this->auth->user(),
			'activeNav' => 'roles',
			'navigationAccess' => $this->navigationAccess($this->auth),
		], $status);
	}

	private function oldValues(?RoleFormData $data, ?array $role = null): array
	{
		return [
			'name' => $data?->name ?? (string) ($role['name'] ?? ''),
			'description' => $data?->description ?? (string) ($role['description'] ?? ''),
			'status' => $data?->active ?? ((int) ($role['status'] ?? 1) === 1),
		];
	}
}
