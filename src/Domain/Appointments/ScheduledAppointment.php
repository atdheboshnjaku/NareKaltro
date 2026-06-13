<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Appointments;

final readonly class ScheduledAppointment
{
	/** @param list<AppointmentService> $services */
	public function __construct(
		public int $id,
		public int $clientId,
		public string $clientName,
		public int $locationId,
		public ?string $locationName,
		public ?int $employeeId,
		public ?string $employeeName,
		public string $startDate,
		public ?string $endDate,
		public string $notes,
		public bool $active,
		public array $services
	) {
	}
}
