<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Clients;

use Fin\Narekaltro\Domain\Shared\PageRequest;
use Fin\Narekaltro\Domain\Shared\PageResult;

interface ClientRepository
{
	/** @return list<ClientProfile> */
	public function activeForAccount(string $accountId, string $search = ''): array;

	/** @return PageResult<ClientProfile> */
	public function activePageForAccount(string $accountId, PageRequest $page, string $search = ''): PageResult;

	public function activeCountForAccount(string $accountId): int;

	public function findActiveForAccount(int $id, string $accountId): ?ClientProfile;

	public function emailExists(string $email, ?int $exceptId = null): bool;

	public function create(string $accountId, ClientFormData $data): int;

	public function update(int $id, string $accountId, ClientFormData $data): void;

	public function deactivate(int $id, string $accountId): void;
}
