<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Appointments;

use DateTimeImmutable;

interface AppointmentCalendarRepository
{
	/** @return list<ScheduledAppointment> */
	public function activeForAccount(
		AppointmentScope $scope,
		?DateTimeImmutable $rangeStart = null,
		?DateTimeImmutable $rangeEnd = null
	): array;

	/** @return list<ScheduledAppointment> */
	public function lastForClient(int $clientId, AppointmentScope $scope, int $limit): array;

	public function clientForAccount(int $clientId, string $accountId): ?AppointmentClient;

	public function upcomingCount(AppointmentScope $scope): int;

	public function findActiveForAccount(int $appointmentId, AppointmentScope $scope): ?ScheduledAppointment;
}
