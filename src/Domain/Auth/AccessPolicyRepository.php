<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Auth;

interface AccessPolicyRepository
{
	public function find(string $accountId): ?AccountAccessPolicy;

	public function save(AccountAccessPolicy $policy, ?int $updatedBy = null): void;
}
