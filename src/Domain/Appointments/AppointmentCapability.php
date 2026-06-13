<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Appointments;

enum AppointmentCapability: string
{
	case CostView = 'appointments.cost.view';
	case CostUpdate = 'appointments.cost.update';
	case EndTimeUse = 'appointments.end_time.use';

	public function label(): string
	{
		return match ($this) {
			self::CostView => 'View appointment costs',
			self::CostUpdate => 'Update appointment costs',
			self::EndTimeUse => 'Use appointment ending time',
		};
	}
}
