<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Auth;

interface AccountPolicyProvisioner
{
	public function provision(string $accountId, ?int $administratorId = null): bool;

	public function provisionExistingAccounts(): PolicyProvisioningSummary;
}
