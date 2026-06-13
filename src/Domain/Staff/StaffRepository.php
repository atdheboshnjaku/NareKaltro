<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Staff;

use Fin\Narekaltro\Domain\Shared\PageRequest;
use Fin\Narekaltro\Domain\Shared\PageResult;

interface StaffRepository
{
	/** @return list<StaffMember> */
	public function activeForAccount(string $accountId): array;

	/** @return PageResult<StaffMember> */
	public function activePageForAccount(string $accountId, PageRequest $page): PageResult;

	public function activeCountForAccount(string $accountId): int;

	public function findActiveForAccount(int $id, string $accountId): ?StaffMember;

	public function findForAccount(int $id, string $accountId): ?StaffMember;

	public function emailExists(string $email, ?int $exceptId = null): bool;

	public function create(string $accountId, StaffFormData $data): int;

	public function update(int $id, string $accountId, StaffFormData $data): void;

	public function deactivate(int $id, string $accountId): void;
}
