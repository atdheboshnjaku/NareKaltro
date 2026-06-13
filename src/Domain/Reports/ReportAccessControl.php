<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Reports;

use Fin\Narekaltro\Domain\Auth\AccessPolicyRepository;
use Fin\Narekaltro\Domain\Auth\AuthenticatedUser;
use Fin\Narekaltro\Domain\Auth\Permission;

final class ReportAccessControl
{
	public function __construct(private AccessPolicyRepository $policies)
	{
	}

	public function scopeFor(AuthenticatedUser $user): ReportScope
	{
		$policy = $this->policies->find($user->accountId);

		if ($policy === null || !$policy->allows($user, Permission::REPORTS_VIEW)) {
			return ReportScope::none($user->accountId);
		}

		$canViewValues = $policy->allows($user, Permission::REPORTS_VALUES_VIEW);

		if ($policy->allows($user, Permission::REPORTS_SCOPE_ACCOUNT)) {
			return ReportScope::account($user->accountId, $canViewValues);
		}

		if ($policy->allows($user, Permission::REPORTS_SCOPE_ASSIGNED_LOCATION)) {
			return ReportScope::locations(
				$user->accountId,
				$user->locationId === null ? [] : [$user->locationId],
				$canViewValues
			);
		}

		return ReportScope::none($user->accountId);
	}
}
