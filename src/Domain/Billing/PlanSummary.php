<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Billing;

final readonly class PlanSummary
{
	/** @param array<string, PlanLimitCheck> $limits */
	public function __construct(
		public AccountSubscription $subscription,
		public SubscriptionPlan $plan,
		public PlanUsageSnapshot $usage,
		private array $limits,
	) {
	}

	public function limit(PlanLimitKey $key): PlanLimitCheck
	{
		return $this->limits[$key->value];
	}

	/** @return array<string, PlanLimitCheck> */
	public function limits(): array
	{
		return $this->limits;
	}
}
