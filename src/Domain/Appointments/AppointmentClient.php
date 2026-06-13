<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Appointments;

final readonly class AppointmentClient
{
	public function __construct(
		public int $id,
		public string $name
	) {
	}
}
