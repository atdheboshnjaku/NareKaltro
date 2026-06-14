<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Appointments;

use DateTimeImmutable;
use Fin\Narekaltro\Domain\Auth\AuthenticatedUser;
use Fin\Narekaltro\Http\NotFoundException;

final class AppointmentCalendar
{
	public function __construct(
		private AppointmentCalendarRepository $appointments,
		private AppointmentAccessControl $access
	) {
	}

	public function upcomingCount(string $accountId): int
	{
		return $this->appointments->upcomingCount(AppointmentScope::account($accountId));
	}

	public function upcomingCountFor(AuthenticatedUser $viewer): int
	{
		return $this->appointments->upcomingCount($this->access->scopeFor($viewer));
	}

	/** @return list<array<string, mixed>> */
	public function events(
		AuthenticatedUser $viewer,
		?DateTimeImmutable $rangeStart = null,
		?DateTimeImmutable $rangeEnd = null
	): array {
		return array_map(
			fn (ScheduledAppointment $appointment): array => $this->event($viewer, $appointment),
			$this->appointments->activeForAccount($this->access->scopeFor($viewer), $rangeStart, $rangeEnd)
		);
	}

	public function recentClientHistory(AuthenticatedUser $viewer, int $clientId): array
	{
		$client = $this->appointments->clientForAccount($clientId, $viewer->accountId)
			?? throw new NotFoundException('Client not found.');

		return [
			'client' => [
				'id' => $client->id,
				'name' => $client->name,
			],
			'entries' => array_map(
				fn (ScheduledAppointment $appointment): array => $this->historyEntry($viewer, $appointment),
				$this->appointments->lastForClient($clientId, $this->access->scopeFor($viewer), 10)
			),
		];
	}

	private function event(AuthenticatedUser $viewer, ScheduledAppointment $appointment): array
	{
		$firstService = $appointment->services[0] ?? null;
		$canUseEndTime = $this->canUseEndTime($viewer, $appointment);
		$endDate = $this->visibleEndDate($appointment, $canUseEndTime);

		return [
			'id' => (string) $appointment->id,
			'title' => $appointment->clientName,
			'start' => $appointment->startDate,
			'end' => $endDate,
			'color' => $firstService?->background ?? '#f1faff',
			'textColor' => $firstService?->color ?? '#009ef7',
			'extendedProps' => [
				'clientId' => $appointment->clientId,
				'clientName' => $appointment->clientName,
				'locationId' => $appointment->locationId,
				'locationName' => $appointment->locationName ?? 'Unavailable location',
				'employeeId' => $appointment->employeeId,
				'employeeName' => $appointment->employeeName ?? 'Unassigned',
				'startDate' => $appointment->startDate,
				'endDate' => $endDate,
				'canUseEndTime' => $canUseEndTime,
				'notes' => $appointment->notes,
				'services' => $this->visibleServices($viewer, $appointment),
			],
		];
	}

	private function historyEntry(AuthenticatedUser $viewer, ScheduledAppointment $appointment): array
	{
		return [
			'appointmentId' => $appointment->id,
			'locationName' => $appointment->locationName ?? 'Unavailable location',
			'employeeName' => $appointment->employeeName ?? 'Unassigned',
			'startDate' => $appointment->startDate,
			'endDate' => $this->visibleEndDate($appointment, $this->canUseEndTime($viewer, $appointment)),
			'notes' => $appointment->notes,
			'active' => $appointment->active,
			'services' => $this->visibleServices($viewer, $appointment),
		];
	}

	/** @return list<array<string, mixed>> */
	private function visibleServices(AuthenticatedUser $viewer, ScheduledAppointment $appointment): array
	{
		$services = [];

		foreach ($appointment->services as $service) {
			$context = $this->context($appointment, $service->id);
			$canSeeCost = $this->access->can($viewer, AppointmentCapability::CostView, $context);
			$services[] = [
				'id' => $service->id,
				'name' => $service->name,
				'background' => $service->background,
				'color' => $service->color,
				'quoteOnly' => $service->quoteOnly,
				'cost' => $canSeeCost ? $service->cost : null,
				'canUpdateCost' => $this->access->canUpdateCosts($viewer, $context),
			];
		}

		return $services;
	}

	private function visibleEndDate(ScheduledAppointment $appointment, bool $canUseEndTime): ?string
	{
		if (
			!$canUseEndTime
			|| $appointment->endDate === null
			|| str_starts_with($appointment->endDate, '1970-01-01')
		) {
			return null;
		}

		return $appointment->endDate;
	}

	private function canUseEndTime(AuthenticatedUser $viewer, ScheduledAppointment $appointment): bool
	{
		if ($appointment->services === []) {
			return $this->access->can($viewer, AppointmentCapability::EndTimeUse, $this->context($appointment));
		}

		foreach ($appointment->services as $service) {
			if (!$this->access->can(
				$viewer,
				AppointmentCapability::EndTimeUse,
				$this->context($appointment, $service->id)
			)) {
				return false;
			}
		}

		return true;
	}

	private function context(ScheduledAppointment $appointment, ?int $serviceId = null): AppointmentPolicyContext
	{
		return new AppointmentPolicyContext(
			appointmentId: $appointment->id,
			locationId: $appointment->locationId,
			serviceId: $serviceId
		);
	}
}
