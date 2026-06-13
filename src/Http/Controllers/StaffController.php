<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Http\Controllers;

use Fin\Narekaltro\Core\Request;
use Fin\Narekaltro\Core\Response;
use Fin\Narekaltro\Core\View;
use Fin\Narekaltro\Domain\Auth\AuthenticatedUser;
use Fin\Narekaltro\Domain\Auth\Authorization;
use Fin\Narekaltro\Domain\Auth\Permission;
use Fin\Narekaltro\Domain\Auth\StaffAccessManager;
use Fin\Narekaltro\Domain\Staff\StaffFormData;
use Fin\Narekaltro\Domain\Staff\StaffManager;
use Fin\Narekaltro\Domain\Staff\StaffMember;
use Fin\Narekaltro\Domain\Staff\StaffValidationFailed;

final class StaffController extends Controller
{
	public function __construct(
		View $view,
		private Authorization $auth,
		private StaffManager $staff,
		private StaffAccessManager $access
	) {
		parent::__construct($view);
	}

	public function index(Request $request): Response
	{
		$user = $this->auth->require(Permission::USERS_VIEW);

		return $this->renderIndex($request, $user);
	}

	public function create(Request $request): Response
	{
		$user = $this->auth->require(Permission::USERS_CREATE);
		$defaults = new StaffFormData($user->locationId ?? 0, '', '', '', []);

		return $this->renderCreate($user, $defaults);
	}

	public function store(Request $request): Response
	{
		$user = $this->auth->require(Permission::USERS_CREATE);
		$data = StaffFormData::fromArray($request->all());

		try {
			$this->staff->create($user, $data);
		} catch (StaffValidationFailed $exception) {
			return $this->renderCreate($user, $data, $exception->errors(), 422);
		}

		return $this->redirect('/users');
	}

	public function edit(Request $request): Response
	{
		$user = $this->auth->require(Permission::USERS_UPDATE);
		$member = $this->staff->find((int) $request->query('id'), $user->accountId);
		$roles = $this->staff->selectedRoles($user->accountId, $member);

		return $this->renderEdit($user, $member, StaffFormData::fromMember($member, $roles));
	}

	public function update(Request $request): Response
	{
		$user = $this->auth->require(Permission::USERS_UPDATE);
		$memberId = (int) $request->input('id');
		$member = $this->staff->find($memberId, $user->accountId);
		$data = StaffFormData::fromArray($request->all());

		try {
			$this->staff->update($user, $memberId, $data);
		} catch (StaffValidationFailed $exception) {
			return $this->renderEdit($user, $member, $data, $exception->errors(), 422);
		}

		return $this->redirect('/users');
	}

	public function deactivate(Request $request): Response
	{
		$user = $this->auth->require(Permission::USERS_DELETE);

		try {
			$this->staff->deactivate($user, (int) $request->input('id'));
		} catch (StaffValidationFailed $exception) {
			$message = (string) (array_first($exception->errors()) ?? 'Unable to deactivate this user.');

			return $this->renderIndex($request, $user, $message, 422);
		}

		return $this->redirect('/users');
	}

	private function renderIndex(
		Request $request,
		AuthenticatedUser $user,
		?string $removeError = null,
		int $status = 200
	): Response {
		$page = $this->pagination($request, 25, 100);
		$staff = $this->access->staffPageWithAccess($user->accountId, $page);

		return $this->render('users.index', [
			'title' => 'Users',
			'staff' => $staff->items,
			'total' => $staff->total,
			'pagination' => $staff,
			'canCreate' => $this->auth->can(Permission::USERS_CREATE),
			'canUpdate' => $this->auth->can(Permission::USERS_UPDATE),
			'canDelete' => $this->auth->can(Permission::USERS_DELETE),
			'canManageAccess' => $this->auth->can(Permission::USERS_ACCESS_MANAGE),
			'removeError' => $removeError,
			'currentUser' => $user,
			'activeNav' => 'users',
			'navigationAccess' => $this->navigationAccess($this->auth),
		], $status);
	}

	private function renderCreate(
		AuthenticatedUser $user,
		StaffFormData $old,
		array $errors = [],
		int $status = 200
	): Response {
		return $this->renderForm('users.create', $user, null, $old, $errors, $status);
	}

	private function renderEdit(
		AuthenticatedUser $user,
		StaffMember $member,
		StaffFormData $old,
		array $errors = [],
		int $status = 200
	): Response {
		return $this->renderForm('users.edit', $user, $member, $old, $errors, $status);
	}

	private function renderForm(
		string $template,
		AuthenticatedUser $user,
		?StaffMember $member,
		StaffFormData $old,
		array $errors,
		int $status
	): Response {
		return $this->render($template, [
			'title' => $member === null ? 'Create User' : 'Edit User',
			'member' => $member,
			'old' => $old,
			'locations' => $this->staff->activeLocations($user->accountId),
			'roles' => $this->staff->rolesForForm($user, $member),
			'errors' => $errors,
			'currentUser' => $user,
			'activeNav' => 'users',
			'navigationAccess' => $this->navigationAccess($this->auth),
		], $status);
	}
}
