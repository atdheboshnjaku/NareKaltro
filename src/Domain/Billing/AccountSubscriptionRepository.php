<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Billing;

interface AccountSubscriptionRepository
{
	public function forAccount(string $accountId): AccountSubscription;

	public function save(AccountSubscription $subscription): void;
}
