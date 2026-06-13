<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Http\Controllers;

use Fin\Narekaltro\Core\Request;
use Fin\Narekaltro\Core\Response;
use Fin\Narekaltro\Core\View;
use Fin\Narekaltro\Domain\Auth\Authorization;
use Fin\Narekaltro\Domain\Auth\Permission;
use Fin\Narekaltro\Domain\Auth\StaffAccessManager;
use Fin\Narekaltro\Domain\Auth\UserAccessFormData;
use Fin\Narekaltro\Domain\Auth\UserAccessValidationFailed;

final class StaffAccessController extends Controller
{
	public function __construct(
		View $view,
		private Authorization $auth,
		private StaffAccessManager $access
	) {
		parent::__construct($view);
	}

	public function edit(Request $request): Response
	{
		$user = $this->auth->require(Permission::USERS_ACCESS_MANAGE);

		return $this->renderEditor($user->accountId, (int) $request->query('id'));
	}

	public function update(Request $request): Response
	{
		$user = $this->auth->require(Permission::USERS_ACCESS_MANAGE);
		$staffId = (int) $request->input('id');
		$data = UserAccessFormData::fromArray($request->all());

		try {
			$this->access->update($user, $staffId, $data);
		} catch (UserAccessValidationFailed $exception) {
			return $this->renderEditor($user->accountId, $staffId, $data, $exception->errors(), 422);
		}

		return $this->redirect('/users/access?id=' . $staffId);
	}

	public function reset(Request $request): Response
	{
		$user = $this->auth->require(Permission::USERS_ACCESS_MANAGE);
		$staffId = (int) $request->input('id');

		try {
			$this->access->reset($user, $staffId);
		} catch (UserAccessValidationFailed $exception) {
			return $this->renderEditor($user->accountId, $staffId, null, $exception->errors(), 422);
		}

		return $this->redirect('/users/access?id=' . $staffId);
	}

	private function renderEditor(
		string $accountId,
		int $staffId,
		?UserAccessFormData $old = null,
		array $errors = [],
		int $status = 200
	): Response {
		$editor = $this->access->editor($accountId, $staffId);
		$selected = $old === null ? $editor['access'] : [
			'customized' => $editor['access']['customized'],
			'roles' => $old->roles,
			'allow' => $old->allow,
			'deny' => $old->deny,
		];

		return $this->render('users.access', [
			'title' => 'Edit User Access',
			'member' => $editor['member'],
			'roles' => $editor['roles'],
			'permissions' => $editor['permissions'],
			'selected' => $selected,
			'errors' => $errors,
			'currentUser' => $this->auth->user(),
			'activeNav' => 'users',
			'navigationAccess' => $this->navigationAccess($this->auth),
		], $status);
	}
}
