<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Clients;

final readonly class ClientHistoryService
{
	public function __construct(
		public int $id,
		public string $name,
		public string $background,
		public string $color,
		public ?string $cost,
	) {
	}
}
