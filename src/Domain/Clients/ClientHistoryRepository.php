<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Clients;

use Fin\Narekaltro\Domain\Shared\PageRequest;
use Fin\Narekaltro\Domain\Shared\PageResult;

interface ClientHistoryRepository
{
	/** @return list<ClientHistoryEntry> */
	public function forClient(int $clientId, string $accountId): array;

	/** @return PageResult<ClientHistoryEntry> */
	public function pageForClient(int $clientId, string $accountId, PageRequest $page): PageResult;
}
