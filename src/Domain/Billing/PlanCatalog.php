<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Billing;

final class PlanCatalog
{
	/** @return list<SubscriptionPlan> */
	public function all(): array
	{
		return [
			$this->get(PlanKey::Free),
			$this->get(PlanKey::Pulse),
			$this->get(PlanKey::Apex),
		];
	}

	public function default(): SubscriptionPlan
	{
		return $this->get(PlanKey::Free);
	}

	public function get(PlanKey $key): SubscriptionPlan
	{
		return match ($key) {
			PlanKey::Free => new SubscriptionPlan(
				key: PlanKey::Free,
				name: 'Free',
				monthlyPrice: '0.00',
				limits: new PlanLimitSet(locations: 2, staffMembers: 20, bookingsPerMonth: 10),
				features: [PlanFeature::BasicReports, PlanFeature::ClientHistory],
				description: 'For trying the system with a controlled booking allowance.'
			),
			PlanKey::Pulse => new SubscriptionPlan(
				key: PlanKey::Pulse,
				name: 'Pulse',
				monthlyPrice: '7.99',
				limits: new PlanLimitSet(locations: 5, staffMembers: 50, bookingsPerMonth: 250),
				features: [PlanFeature::BasicReports, PlanFeature::AccessPolicies, PlanFeature::ClientHistory],
				description: 'For active teams that need daily scheduling, staff controls, and useful reporting.'
			),
			PlanKey::Apex => new SubscriptionPlan(
				key: PlanKey::Apex,
				name: 'Apex',
				monthlyPrice: '13.99',
				limits: new PlanLimitSet(locations: 25, staffMembers: 250, bookingsPerMonth: 2500),
				features: [
					PlanFeature::BasicReports,
					PlanFeature::AdvancedReports,
					PlanFeature::AccessPolicies,
					PlanFeature::ClientHistory,
					PlanFeature::PrioritySupport,
				],
				description: 'For larger operations that need advanced analytics and much higher usage limits.'
			),
		};
	}
}
