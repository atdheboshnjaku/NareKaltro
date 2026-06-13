<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Appointments;

final readonly class AppointmentPolicyDecision
{
	public function __construct(
		public bool $allowed,
		public string $source,
		public ?string $ruleId = null,
		public ?string $ruleName = null
	) {
	}
}
