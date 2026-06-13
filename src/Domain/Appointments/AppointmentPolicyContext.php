<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Appointments;

use InvalidArgumentException;

final readonly class AppointmentPolicyContext
{
	public function __construct(
		public ?int $appointmentId = null,
		public ?int $locationId = null,
		public ?int $serviceId = null
	) {
		foreach ([
			'appointmentId' => $this->appointmentId,
			'locationId' => $this->locationId,
			'serviceId' => $this->serviceId,
		] as $field => $id) {
			if ($id !== null && $id < 1) {
				throw new InvalidArgumentException("{$field} must be a positive ID when supplied.");
			}
		}
	}
}
