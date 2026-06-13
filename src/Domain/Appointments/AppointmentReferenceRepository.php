<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Appointments;

interface AppointmentReferenceRepository
{
	public function existsForAccount(int $appointmentId, string $accountId): bool;
}
