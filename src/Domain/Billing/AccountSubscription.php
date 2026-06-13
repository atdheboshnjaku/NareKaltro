<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Billing;

use DateTimeImmutable;

final readonly class AccountSubscription
{
	public function __construct(
		public string $accountId,
		public PlanKey $planKey,
		public AccountSubscriptionStatus $status,
		public ?DateTimeImmutable $trialEndsAt,
		public ?DateTimeImmutable $currentPeriodStartsAt,
		public ?DateTimeImmutable $currentPeriodEndsAt,
		public ?DateTimeImmutable $createdAt,
		public ?DateTimeImmutable $updatedAt,
	) {
	}

	public static function defaultForAccount(string $accountId): self
	{
		return new self(
			accountId: $accountId,
			planKey: PlanKey::Free,
			status: AccountSubscriptionStatus::Active,
			trialEndsAt: null,
			currentPeriodStartsAt: null,
			currentPeriodEndsAt: null,
			createdAt: null,
			updatedAt: null,
		);
	}

	public function effectivePlanKey(): PlanKey
	{
		if ($this->planKey === PlanKey::Free || $this->status->canUsePaidEntitlements()) {
			return $this->planKey;
		}

		return PlanKey::Free;
	}

	public function withPlan(PlanKey $planKey, AccountSubscriptionStatus $status = AccountSubscriptionStatus::Active): self
	{
		return new self(
			accountId: $this->accountId,
			planKey: $planKey,
			status: $status,
			trialEndsAt: $this->trialEndsAt,
			currentPeriodStartsAt: $this->currentPeriodStartsAt,
			currentPeriodEndsAt: $this->currentPeriodEndsAt,
			createdAt: $this->createdAt,
			updatedAt: $this->updatedAt,
		);
	}
}
