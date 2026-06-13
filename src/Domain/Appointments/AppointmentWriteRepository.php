<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Appointments;

interface AppointmentWriteRepository
{
	public function create(string $accountId, AppointmentFormData $data): int;

	/** @param list<int> $editableCostServiceIds */
	public function update(
		int $appointmentId,
		string $accountId,
		AppointmentFormData $data,
		array $editableCostServiceIds
	): void;

	public function reschedule(
		int $appointmentId,
		string $accountId,
		string $startDate,
		?string $endDate,
		bool $mayUpdateEndDate
	): void;

	public function cancel(int $appointmentId, string $accountId): void;
}
