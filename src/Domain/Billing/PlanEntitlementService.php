<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Billing;

use DateTimeImmutable;

final class PlanEntitlementService
{
	public function __construct(
		private PlanCatalog $catalog,
		private AccountSubscriptionRepository $subscriptions,
		private PlanUsageRepository $usage,
	) {
	}

	public function summaryForAccount(string $accountId, ?DateTimeImmutable $month = null): PlanSummary
	{
		$subscription = $this->subscriptions->forAccount($accountId);
		$plan = $this->catalog->get($subscription->effectivePlanKey());
		$usage = $this->usage->forAccount($accountId, $month ?? new DateTimeImmutable('now'));
		$limits = [];

		foreach (PlanLimitKey::cases() as $key) {
			$limits[$key->value] = new PlanLimitCheck(
				key: $key,
				used: $usage->value($key),
				limit: $plan->limits->value($key),
			);
		}

		return new PlanSummary($subscription, $plan, $usage, $limits);
	}

	public function canCreateLocation(string $accountId): bool
	{
		return $this->summaryForAccount($accountId)->limit(PlanLimitKey::Locations)->allowsAdditional();
	}

	public function canCreateStaffMember(string $accountId): bool
	{
		return $this->summaryForAccount($accountId)->limit(PlanLimitKey::StaffMembers)->allowsAdditional();
	}

	public function canCreateBooking(string $accountId, ?DateTimeImmutable $month = null): bool
	{
		return $this->summaryForAccount($accountId, $month)->limit(PlanLimitKey::BookingsPerMonth)->allowsAdditional();
	}
}
