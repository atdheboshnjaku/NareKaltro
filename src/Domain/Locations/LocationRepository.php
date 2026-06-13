<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Locations;

use Fin\Narekaltro\Domain\Shared\PageRequest;
use Fin\Narekaltro\Domain\Shared\PageResult;

interface LocationRepository
{
	/** @return list<BusinessLocation> */
	public function activeForAccount(string $accountId): array;

	/** @return PageResult<BusinessLocation> */
	public function activePageForAccount(string $accountId, PageRequest $page): PageResult;

	public function activeCountForAccount(string $accountId): int;

	public function findActiveForAccount(int $id, string $accountId): ?BusinessLocation;

	public function findForAccount(int $id, string $accountId): ?BusinessLocation;

	public function activeNameExists(string $accountId, string $name, ?int $exceptId = null): bool;

	public function create(string $accountId, LocationFormData $data): int;

	public function update(int $id, string $accountId, LocationFormData $data): void;

	public function deactivate(int $id, string $accountId): void;
}
