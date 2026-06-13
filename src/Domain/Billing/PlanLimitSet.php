<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Billing;

final readonly class PlanLimitSet
{
	public function __construct(
		public ?int $locations,
		public ?int $staffMembers,
		public ?int $bookingsPerMonth,
	) {
	}

	public function value(PlanLimitKey $key): ?int
	{
		return match ($key) {
			PlanLimitKey::Locations => $this->locations,
			PlanLimitKey::StaffMembers => $this->staffMembers,
			PlanLimitKey::BookingsPerMonth => $this->bookingsPerMonth,
		};
	}
}
