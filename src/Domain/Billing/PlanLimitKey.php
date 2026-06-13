<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Billing;

enum PlanLimitKey: string
{
	case Locations = 'locations';
	case StaffMembers = 'staff_members';
	case BookingsPerMonth = 'bookings_per_month';
}
