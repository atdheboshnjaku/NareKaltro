<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Appointments;

use Fin\Narekaltro\Domain\Auth\AccessPolicyRepository;
use Fin\Narekaltro\Domain\Auth\AuthenticatedUser;
use Fin\Narekaltro\Domain\Auth\Permission;

final class AppointmentAccessControl
{
	public function __construct(private AccessPolicyRepository $policies)
	{
	}

	public function decision(
		AuthenticatedUser $user,
		AppointmentCapability $capability,
		AppointmentPolicyContext $context
	): AppointmentPolicyDecision {
		$policy = $this->policies->find($user->accountId);

		return $policy?->appointmentDecision($user, $capability, $context)
			?? new AppointmentPolicyDecision(false, 'missing_account_policy');
	}

	public function can(
		AuthenticatedUser $user,
		AppointmentCapability $capability,
		AppointmentPolicyContext $context
	): bool {
		return $this->decision($user, $capability, $context)->allowed;
	}

	public function scopeFor(AuthenticatedUser $user): AppointmentScope
	{
		$policy = $this->policies->find($user->accountId);

		if ($policy === null || !$policy->allows($user, Permission::APPOINTMENTS_VIEW)) {
			return AppointmentScope::none($user->accountId);
		}

		$canViewOwn = $policy->allows($user, Permission::APPOINTMENTS_SCOPE_ASSIGNED_EMPLOYEE);
		$employeeId = $canViewOwn ? $user->id : null;

		if ($policy->allows($user, Permission::APPOINTMENTS_SCOPE_ACCOUNT)) {
			return AppointmentScope::account($user->accountId);
		}

		if ($policy->allows($user, Permission::APPOINTMENTS_SCOPE_ASSIGNED_LOCATION)) {
			return AppointmentScope::locations(
				$user->accountId,
				$user->locationId === null ? [] : [$user->locationId],
				$employeeId
			);
		}

		if ($canViewOwn) {
			return AppointmentScope::account($user->accountId, $user->id);
		}

		return AppointmentScope::none($user->accountId);
	}

	public function canAccessLocation(AuthenticatedUser $user, int $locationId): bool
	{
		return $this->scopeFor($user)->includesLocation($locationId);
	}

	public function canUpdateCosts(AuthenticatedUser $user, AppointmentPolicyContext $context): bool
	{
		return $this->can($user, AppointmentCapability::CostView, $context)
			&& $this->can($user, AppointmentCapability::CostUpdate, $context);
	}
}
