<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Appointments;

enum AppointmentRuleEffect: string
{
	case Allow = 'allow';
	case Deny = 'deny';
}
