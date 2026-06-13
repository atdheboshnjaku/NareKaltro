<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Auth;

final class AccessControl
{
	public function __construct(private AccessPolicyRepository $policies)
	{
	}

	public function can(?AuthenticatedUser $user, string $permission): bool
	{
		if ($user === null) {
			return false;
		}

		$policy = $this->policies->find($user->accountId);

		return $policy?->allows($user, $permission) ?? false;
	}

	public function hasRole(?AuthenticatedUser $user, string $roleId): bool
	{
		if ($user === null) {
			return false;
		}

		$policy = $this->policies->find($user->accountId);

		return $policy?->hasRole($user, $roleId) ?? false;
	}
}
