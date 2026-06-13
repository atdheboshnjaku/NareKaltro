<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Staff;

use Fin\Narekaltro\Domain\Auth\AccessPolicyRepository;
use Fin\Narekaltro\Domain\Auth\AccountAccessPolicy;
use Fin\Narekaltro\Domain\Auth\AuthenticatedUser;
use Fin\Narekaltro\Domain\Auth\Permission;
use Fin\Narekaltro\Domain\Auth\UserAccessFormData;
use Fin\Narekaltro\Domain\Locations\BusinessLocation;
use Fin\Narekaltro\Domain\Locations\LocationRepository;
use Fin\Narekaltro\Domain\Shared\TransactionManager;
use Fin\Narekaltro\Http\NotFoundException;

final class StaffManager
{
	public function __construct(
		private StaffRepository $staff,
		private LocationRepository $locations,
		private AccessPolicyRepository $policies,
		private TransactionManager $transactions
	) {
	}

	public function find(int $id, string $accountId): StaffMember
	{
		return $this->staff->findActiveForAccount($id, $accountId)
			?? throw new NotFoundException('Staff user not found.');
	}

	/** @return list<BusinessLocation> */
	public function activeLocations(string $accountId): array
	{
		return $this->locations->activeForAccount($accountId);
	}

	public function selectedRoles(string $accountId, StaffMember $member): array
	{
		return $this->policy($accountId)->userAccess($member->id, $member->roleId)['roles'];
	}

	public function rolesForForm(AuthenticatedUser $actor, ?StaffMember $member = null): array
	{
		$policy = $this->policy($actor->accountId);
		$existing = $member === null ? [] : $this->selectedRoles($actor->accountId, $member);
		$canManageAccess = $policy->allows($actor, Permission::USERS_ACCESS_MANAGE);
		$roles = [];

		foreach ($policy->rolesForAssignment() as $role) {
			$selected = in_array($role['id'], $existing, true);
			$editable = $this->canAssignRole($policy, $actor, $role['id'], $canManageAccess);

			if (!$selected && !$editable) {
				continue;
			}

			$role['editable'] = $editable || $canManageAccess;
			$roles[] = $role;
		}

		return $roles;
	}

	public function create(AuthenticatedUser $actor, StaffFormData $data): int
	{
		$policy = $this->policy($actor->accountId);
		$this->assertValid($actor, $policy, $data);

		return $this->transactions->transactional(function () use ($actor, $data, $policy): int {
			$staffId = $this->staff->create($actor->accountId, $data);
			$candidate = $policy->withUserAccess(
				$staffId,
				new UserAccessFormData($data->roles, [], [])
			);
			$this->policies->save($candidate, $actor->id);

			return $staffId;
		});
	}

	public function update(AuthenticatedUser $actor, int $id, StaffFormData $data): void
	{
		$member = $this->find($id, $actor->accountId);
		$policy = $this->policy($actor->accountId);
		$access = $policy->userAccess($member->id, $member->roleId);
		$this->assertValid($actor, $policy, $data, $member, $access['roles']);

		$rolesChanged = !$this->sameRoles($access['roles'], $data->roles);
		$candidate = $policy;

		if ($rolesChanged) {
			$candidate = $policy->withUserAccess(
				$member->id,
				new UserAccessFormData($data->roles, $access['allow'], $access['deny'])
			);
			$this->assertRequiredManagersRemain($candidate);
		}

		$this->transactions->transactional(function () use ($actor, $id, $data, $candidate, $rolesChanged): void {
			$this->staff->update($id, $actor->accountId, $data);

			if ($rolesChanged) {
				$this->policies->save($candidate, $actor->id);
			}
		});
	}

	public function deactivate(AuthenticatedUser $actor, int $id): void
	{
		$member = $this->find($id, $actor->accountId);
		$policy = $this->policy($actor->accountId);
		$access = $policy->userAccess($member->id, $member->roleId);
		$candidate = $access['customized'] ? $policy->withoutUserAccess($member->id) : $policy;
		$this->assertRequiredManagersRemain($candidate, $member->id);

		$this->transactions->transactional(function () use ($actor, $member, $candidate, $access): void {
			$this->staff->deactivate($member->id, $actor->accountId);

			if ($access['customized']) {
				$this->policies->save($candidate, $actor->id);
			}
		});
	}

	private function assertValid(
		AuthenticatedUser $actor,
		AccountAccessPolicy $policy,
		StaffFormData $data,
		?StaffMember $existing = null,
		array $existingRoles = []
	): void {
		$errors = $data->validate($existing === null);

		$keepsExistingLocation = $existing !== null && $data->locationId === $existing->locationId;
		if (!$keepsExistingLocation && $this->locations->findActiveForAccount($data->locationId, $actor->accountId) === null) {
			$errors['location_id'] = 'Please select an active user location.';
		}

		if (
			$data->email !== ''
			&& $this->staff->emailExists($data->email, $existing?->id)
		) {
			$errors['email'] = 'This user email already exists.';
		}

		$roleErrors = $policy->validateUserAccess(new UserAccessFormData($data->roles, [], []));
		if (isset($roleErrors['roles'])) {
			$errors['roles'] = $roleErrors['roles'];
		}

		$canManageAccess = $policy->allows($actor, Permission::USERS_ACCESS_MANAGE);
		foreach ($data->roles as $roleId) {
			if (
				!in_array($roleId, $existingRoles, true)
				&& !$this->canAssignRole($policy, $actor, $roleId, $canManageAccess)
			) {
				$errors['roles'] = 'You cannot assign one of the selected roles.';
				break;
			}
		}

		if (!$canManageAccess) {
			foreach ($existingRoles as $roleId) {
				if (
					!$this->canAssignRole($policy, $actor, $roleId, false)
					&& !in_array($roleId, $data->roles, true)
				) {
					$errors['roles'] = 'You cannot remove an existing elevated role assignment.';
					break;
				}
			}
		}

		if ($errors !== []) {
			throw new StaffValidationFailed($errors);
		}
	}

	private function canAssignRole(
		AccountAccessPolicy $policy,
		AuthenticatedUser $actor,
		string $roleId,
		bool $canManageAccess
	): bool {
		$role = $policy->findRole($roleId);
		if ($role === null || !$role['status']) {
			return false;
		}

		if ($canManageAccess) {
			return true;
		}

		foreach ($role['permissions'] as $permission) {
			if (!$policy->allows($actor, $permission)) {
				return false;
			}
		}

		return true;
	}

	private function assertRequiredManagersRemain(AccountAccessPolicy $policy, ?int $excludedId = null): void
	{
		$this->assertPermissionHolderRemains(
			$policy,
			Permission::USERS_ACCESS_MANAGE,
			'At least one active staff user must retain permission to manage staff access.',
			$excludedId
		);
		$this->assertPermissionHolderRemains(
			$policy,
			Permission::ROLES_MANAGE,
			'At least one active staff user must retain permission to manage roles.',
			$excludedId
		);
	}

	private function assertPermissionHolderRemains(
		AccountAccessPolicy $policy,
		string $permission,
		string $message,
		?int $excludedId
	): void {
		foreach ($this->staff->activeForAccount($policy->accountId) as $member) {
			if ($member->id === $excludedId) {
				continue;
			}

			if ($policy->allows($member->authenticatedUser(), $permission)) {
				return;
			}
		}

		throw new StaffValidationFailed(['policy' => $message]);
	}

	private function policy(string $accountId): AccountAccessPolicy
	{
		return $this->policies->find($accountId)
			?? throw new NotFoundException('Access policy not found for this account.');
	}

	private function sameRoles(array $left, array $right): bool
	{
		sort($left);
		sort($right);

		return $left === $right;
	}
}
