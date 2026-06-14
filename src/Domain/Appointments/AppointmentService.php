<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Appointments;

final readonly class AppointmentService
{
	public function __construct(
		public int $id,
		public string $name,
		public string $background,
		public string $color,
		public bool $quoteOnly,
		public ?string $cost
	) {
	}
}
