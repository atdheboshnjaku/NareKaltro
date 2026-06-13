<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Staff;

use Fin\Narekaltro\Domain\Shared\CacheStore;
use Fin\Narekaltro\Domain\Shared\PageRequest;
use Fin\Narekaltro\Domain\Shared\PageResult;
use Fin\Narekaltro\Domain\Staff\StaffFormData;
use Fin\Narekaltro\Domain\Staff\StaffMember;
use Fin\Narekaltro\Domain\Staff\StaffRepository;
use Fin\Narekaltro\Infrastructure\Cache\CacheKey;

final class CachedStaffRepository implements StaffRepository
{
	private const TTL_SECONDS = 900;

	public function __construct(
		private StaffRepository $inner,
		private CacheStore $cache
	) {
	}

	#[\Override]
	public function activeForAccount(string $accountId): array
	{
		return $this->cache->remember(
			$this->key($accountId, 'active'),
			self::TTL_SECONDS,
			fn (): array => $this->inner->activeForAccount($accountId)
		);
	}

	#[\Override]
	public function activePageForAccount(string $accountId, PageRequest $page): PageResult
	{
		return $this->cache->remember(
			$this->key($accountId, 'page', $page->page, $page->perPage),
			self::TTL_SECONDS,
			fn (): PageResult => $this->inner->activePageForAccount($accountId, $page)
		);
	}

	#[\Override]
	public function activeCountForAccount(string $accountId): int
	{
		return $this->cache->remember(
			$this->key($accountId, 'count'),
			self::TTL_SECONDS,
			fn (): int => $this->inner->activeCountForAccount($accountId)
		);
	}

	#[\Override]
	public function findActiveForAccount(int $id, string $accountId): ?StaffMember
	{
		return $this->cache->remember(
			$this->key($accountId, 'find-active', $id),
			self::TTL_SECONDS,
			fn (): ?StaffMember => $this->inner->findActiveForAccount($id, $accountId)
		);
	}

	#[\Override]
	public function findForAccount(int $id, string $accountId): ?StaffMember
	{
		return $this->cache->remember(
			$this->key($accountId, 'find', $id),
			self::TTL_SECONDS,
			fn (): ?StaffMember => $this->inner->findForAccount($id, $accountId)
		);
	}

	#[\Override]
	public function emailExists(string $email, ?int $exceptId = null): bool
	{
		return $this->inner->emailExists($email, $exceptId);
	}

	#[\Override]
	public function create(string $accountId, StaffFormData $data): int
	{
		$id = $this->inner->create($accountId, $data);
		$this->flushAccount($accountId);

		return $id;
	}

	#[\Override]
	public function update(int $id, string $accountId, StaffFormData $data): void
	{
		$this->inner->update($id, $accountId, $data);
		$this->flushAccount($accountId);
	}

	#[\Override]
	public function deactivate(int $id, string $accountId): void
	{
		$this->inner->deactivate($id, $accountId);
		$this->flushAccount($accountId);
	}

	private function flushAccount(string $accountId): void
	{
		$this->cache->forgetByPrefix($this->prefix($accountId));
	}

	private function prefix(string $accountId): string
	{
		return CacheKey::accountPrefix('staff', $accountId);
	}

	private function key(string $accountId, string|int ...$parts): string
	{
		return CacheKey::account('staff', $accountId, ...$parts);
	}
}
