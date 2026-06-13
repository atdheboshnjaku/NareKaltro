<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Billing;

final readonly class SubscriptionPlan
{
	/** @param list<PlanFeature> $features */
	public function __construct(
		public PlanKey $key,
		public string $name,
		public string $monthlyPrice,
		public PlanLimitSet $limits,
		private array $features,
		public string $description = '',
	) {
	}

	/** @return list<PlanFeature> */
	public function features(): array
	{
		return $this->features;
	}

	public function hasFeature(PlanFeature $feature): bool
	{
		return in_array($feature, $this->features, true);
	}
}
