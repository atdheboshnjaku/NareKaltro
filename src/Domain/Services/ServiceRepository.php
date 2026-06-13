<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Services;

use Fin\Narekaltro\Domain\Shared\PageRequest;
use Fin\Narekaltro\Domain\Shared\PageResult;

interface ServiceRepository
{
	/** @return list<ServiceOffering> */
	public function activeForAccount(string $accountId): array;

	/** @return PageResult<ServiceOffering> */
	public function activePageForAccount(string $accountId, PageRequest $page): PageResult;

	public function activeCountForAccount(string $accountId): int;

	public function findForAccount(int $id, string $accountId): ?ServiceOffering;

	public function activeNameExists(string $accountId, string $name, ?int $exceptId = null): bool;

	public function create(string $accountId, ServiceFormData $data): int;

	public function update(int $id, string $accountId, ServiceFormData $data): void;

	public function deactivate(int $id, string $accountId): void;
}
