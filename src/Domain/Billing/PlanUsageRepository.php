<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Billing;

use DateTimeImmutable;

interface PlanUsageRepository
{
	public function forAccount(string $accountId, DateTimeImmutable $month): PlanUsageSnapshot;
}
