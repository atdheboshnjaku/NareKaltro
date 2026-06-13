<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Auth;

use Fin\Narekaltro\Domain\Shared\PageRequest;
use Fin\Narekaltro\Domain\Shared\PageResult;
use Fin\Narekaltro\Domain\Staff\StaffMember;
use Fin\Narekaltro\Domain\Staff\StaffRepository;
use Fin\Narekaltro\Http\NotFoundException;

final class StaffAccessManager
{
	public function __construct(
		private StaffRepository $staff,
		private AccessPolicyRepository $policies
	) {
	}

	public function staffWithAccess(string $accountId): array
	{
		$policy = $this->policy($accountId);
		$rows = [];

		foreach ($this->staff->activeForAccount($accountId) as $member) {
			$rows[] = $this->accessRow($policy, $member);
		}

		return $rows;
	}

	public function staffPageWithAccess(string $accountId, PageRequest $page): PageResult
	{
		$policy = $this->policy($accountId);
		$staffPage = $this->staff->activePageForAccount($accountId, $page);

		return new PageResult(
			array_map(
				fn (StaffMember $member): array => $this->accessRow($policy, $member),
				$staffPage->items
			),
			$staffPage->total,
			$staffPage->request
		);
	}

	public function editor(string $accountId, int $staffId): array
	{
		$member = $this->member($staffId, $accountId);
		$policy = $this->policy($accountId);
		$groups = [];

		foreach ($policy->permissions() as $permission) {
			$groups[$permission['permission_group']][] = $permission;
		}

		return [
			'member' => $member,
			'roles' => $policy->rolesForAssignment(),
			'permissions' => $groups,
			'access' => $policy->userAccess($member->id, $member->roleId),
		];
	}

	public function update(AuthenticatedUser $actor, int $staffId, UserAccessFormData $data): void
	{
		$member = $this->member($staffId, $actor->accountId);
		$policy = $this->policy($actor->accountId);
		$errors = $policy->validateUserAccess($data);
		$existingRoles = $policy->userAccess($member->id, $member->roleId)['roles'];
		$roleStatus = [];

		foreach ($policy->rolesForAssignment() as $role) {
			$roleStatus[$role['id']] = $role['active'];
		}

		foreach ($data->roles as $roleId) {
			if (!($roleStatus[$roleId] ?? false) && !in_array($roleId, $existingRoles, true)) {
				$errors['roles'] = 'An inactive role cannot be newly assigned.';
				break;
			}
		}

		if ($errors !== []) {
			throw new UserAccessValidationFailed($errors);
		}

		$candidate = $policy->withUserAccess($member->id, $data);
		$this->assertRequiredManagersRemain($candidate);
		$this->policies->save($candidate, $actor->id);
	}

	public function reset(AuthenticatedUser $actor, int $staffId): void
	{
		$member = $this->member($staffId, $actor->accountId);
		$policy = $this->policy($actor->accountId);

		if (!$policy->userAccess($member->id, $member->roleId)['customized']) {
			return;
		}

		$candidate = $policy->withoutUserAccess($member->id);
		$this->assertRequiredManagersRemain($candidate);
		$this->policies->save($candidate, $actor->id);
	}

	private function member(int $staffId, string $accountId): StaffMember
	{
		return $this->staff->findActiveForAccount($staffId, $accountId)
			?? throw new NotFoundException('Staff user not found.');
	}

	private function policy(string $accountId): AccountAccessPolicy
	{
		return $this->policies->find($accountId)
			?? throw new NotFoundException('Access policy not found for this account.');
	}

	private function accessRow(AccountAccessPolicy $policy, StaffMember $member): array
	{
		$access = $policy->userAccess($member->id, $member->roleId);

		return [
			'member' => $member,
			'roles' => $policy->roleNames($access['roles']),
			'customized' => $access['customized'],
		];
	}

	private function assertRequiredManagersRemain(AccountAccessPolicy $policy): void
	{
		$this->assertPermissionHolderRemains(
			$policy,
			Permission::USERS_ACCESS_MANAGE,
			'At least one active staff user must retain permission to manage staff access.'
		);
		$this->assertPermissionHolderRemains(
			$policy,
			Permission::ROLES_MANAGE,
			'At least one active staff user must retain permission to manage roles.'
		);
	}

	private function assertPermissionHolderRemains(
		AccountAccessPolicy $policy,
		string $permission,
		string $message
	): void {
		foreach ($this->staff->activeForAccount($policy->accountId) as $member) {
			if ($policy->allows($member->authenticatedUser(), $permission)) {
				return;
			}
		}

		throw new UserAccessValidationFailed(['policy' => $message]);
	}
}
