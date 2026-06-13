<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Auth;

use Fin\Narekaltro\Domain\Shared\PageRequest;
use Fin\Narekaltro\Domain\Shared\PageResult;
use Fin\Narekaltro\Domain\Staff\StaffRepository;
use Fin\Narekaltro\Http\NotFoundException;

final class RoleManager
{
	public function __construct(
		private AccessPolicyRepository $policies,
		private StaffRepository $staff
	) {
	}

	public function rolesWithPermissions(string $accountId): array
	{
		return $this->policy($accountId)->rolesWithPermissions();
	}

	public function rolesPage(string $accountId, PageRequest $page): PageResult
	{
		return PageResult::fromItems($this->rolesWithPermissions($accountId), $page);
	}

	public function find(string $accountId, string $roleId): array
	{
		return $this->policy($accountId)->findRole($roleId)
			?? throw new NotFoundException('Role not found.');
	}

	public function permissionGroups(string $accountId): array
	{
		$groups = [];

		foreach ($this->policy($accountId)->permissions() as $permission) {
			$groups[$permission['permission_group']][] = $permission;
		}

		return $groups;
	}

	public function create(string $accountId, int $updatedBy, RoleFormData $data): string
	{
		$this->assertValid($data);
		$policy = $this->policy($accountId);
		$roleId = $policy->addRole($data);
		$this->policies->save($policy, $updatedBy);

		return $roleId;
	}

	public function update(string $accountId, int $updatedBy, string $roleId, RoleFormData $data): void
	{
		$this->assertValid($data);
		$policy = $this->policy($accountId);
		$candidate = $policy->withUpdatedRole($roleId, $data);
		$this->assertPermissionHolderRemains(
			$candidate,
			Permission::ROLES_MANAGE,
			'At least one active staff user must retain permission to manage roles.'
		);
		$this->assertPermissionHolderRemains(
			$candidate,
			Permission::USERS_ACCESS_MANAGE,
			'At least one active staff user must retain permission to manage staff access.'
		);
		$this->policies->save($candidate, $updatedBy);
	}

	private function policy(string $accountId): AccountAccessPolicy
	{
		return $this->policies->find($accountId)
			?? throw new NotFoundException('Access policy not found for this account.');
	}

	private function assertValid(RoleFormData $data): void
	{
		$errors = $data->validate();

		if ($errors !== []) {
			throw new RoleValidationFailed($errors);
		}
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

		throw new RoleValidationFailed(['policy' => $message]);
	}
}
