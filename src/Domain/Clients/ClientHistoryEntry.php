<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Clients;

final readonly class ClientHistoryEntry
{
	/** @param list<ClientHistoryService> $services */
	public function __construct(
		public int $appointmentId,
		public int $locationId,
		public ?string $locationName,
		public string $startDate,
		public ?string $endDate,
		public string $notes,
		public bool $active,
		public array $services,
	) {
	}
}
