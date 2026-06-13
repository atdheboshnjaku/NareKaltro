<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Clients;

use Fin\Narekaltro\Domain\Appointments\AppointmentAccessControl;
use Fin\Narekaltro\Domain\Appointments\AppointmentCapability;
use Fin\Narekaltro\Domain\Appointments\AppointmentPolicyContext;
use Fin\Narekaltro\Domain\Auth\AuthenticatedUser;
use Fin\Narekaltro\Domain\Shared\PageRequest;
use Fin\Narekaltro\Domain\Shared\PageResult;

final class ClientHistory
{
	public function __construct(
		private ClientHistoryRepository $history,
		private AppointmentAccessControl $access
	) {
	}

	/** @return list<ClientHistoryEntry> */
	public function forClient(int $clientId, string $accountId, AuthenticatedUser $viewer): array
	{
		return $this->visibleEntries($this->history->forClient($clientId, $accountId), $viewer);
	}

	public function pageForClient(
		int $clientId,
		string $accountId,
		AuthenticatedUser $viewer,
		PageRequest $page
	): PageResult {
		$history = $this->history->pageForClient($clientId, $accountId, $page);

		return new PageResult(
			$this->visibleEntries($history->items, $viewer),
			$history->total,
			$history->request
		);
	}

	/**
	 * @param list<ClientHistoryEntry> $history
	 * @return list<ClientHistoryEntry>
	 */
	private function visibleEntries(array $history, AuthenticatedUser $viewer): array
	{
		$entries = [];

		foreach ($history as $entry) {
			$services = [];

			foreach ($entry->services as $service) {
				$context = new AppointmentPolicyContext(
					appointmentId: $entry->appointmentId,
					locationId: $entry->locationId,
					serviceId: $service->id
				);
				$services[] = new ClientHistoryService(
					id: $service->id,
					name: $service->name,
					background: $service->background,
					color: $service->color,
					cost: $this->access->can($viewer, AppointmentCapability::CostView, $context)
						? $service->cost
						: null
				);
			}

			$entries[] = new ClientHistoryEntry(
				appointmentId: $entry->appointmentId,
				locationId: $entry->locationId,
				locationName: $entry->locationName,
				startDate: $entry->startDate,
				endDate: $this->visibleEndDate($viewer, $entry),
				notes: $entry->notes,
				active: $entry->active,
				services: $services
			);
		}

		return $entries;
	}

	private function visibleEndDate(AuthenticatedUser $viewer, ClientHistoryEntry $entry): ?string
	{
		if ($entry->endDate === null || str_starts_with($entry->endDate, '1970-01-01')) {
			return null;
		}

		if ($entry->services === []) {
			$context = new AppointmentPolicyContext(
				appointmentId: $entry->appointmentId,
				locationId: $entry->locationId
			);

			return $this->access->can($viewer, AppointmentCapability::EndTimeUse, $context)
				? $entry->endDate
				: null;
		}

		foreach ($entry->services as $service) {
			$context = new AppointmentPolicyContext(
				appointmentId: $entry->appointmentId,
				locationId: $entry->locationId,
				serviceId: $service->id
			);

			if (!$this->access->can($viewer, AppointmentCapability::EndTimeUse, $context)) {
				return null;
			}
		}

		return $entry->endDate;
	}
}
