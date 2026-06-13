<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Billing;

final readonly class PlanUsageSnapshot
{
	public function __construct(
		public int $activeLocations,
		public int $activeStaffMembers,
		public int $bookingsThisMonth,
	) {
	}

	public function value(PlanLimitKey $key): int
	{
		return match ($key) {
			PlanLimitKey::Locations => $this->activeLocations,
			PlanLimitKey::StaffMembers => $this->activeStaffMembers,
			PlanLimitKey::BookingsPerMonth => $this->bookingsThisMonth,
		};
	}
}
