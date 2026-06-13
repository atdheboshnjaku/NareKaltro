<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Locations;

use Fin\Narekaltro\Domain\Locations\BusinessLocation;
use Fin\Narekaltro\Domain\Locations\LocationFormData;
use Fin\Narekaltro\Domain\Locations\LocationRepository;
use Fin\Narekaltro\Domain\Shared\CacheStore;
use Fin\Narekaltro\Domain\Shared\PageRequest;
use Fin\Narekaltro\Domain\Shared\PageResult;
use Fin\Narekaltro\Infrastructure\Cache\CacheKey;

final class CachedLocationRepository implements LocationRepository
{
	private const TTL_SECONDS = 900;

	public function __construct(
		private LocationRepository $inner,
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
	public function findActiveForAccount(int $id, string $accountId): ?BusinessLocation
	{
		return $this->cache->remember(
			$this->key($accountId, 'find-active', $id),
			self::TTL_SECONDS,
			fn (): ?BusinessLocation => $this->inner->findActiveForAccount($id, $accountId)
		);
	}

	#[\Override]
	public function findForAccount(int $id, string $accountId): ?BusinessLocation
	{
		return $this->cache->remember(
			$this->key($accountId, 'find', $id),
			self::TTL_SECONDS,
			fn (): ?BusinessLocation => $this->inner->findForAccount($id, $accountId)
		);
	}

	#[\Override]
	public function activeNameExists(string $accountId, string $name, ?int $exceptId = null): bool
	{
		return $this->inner->activeNameExists($accountId, $name, $exceptId);
	}

	#[\Override]
	public function create(string $accountId, LocationFormData $data): int
	{
		$id = $this->inner->create($accountId, $data);
		$this->flushAccount($accountId);

		return $id;
	}

	#[\Override]
	public function update(int $id, string $accountId, LocationFormData $data): void
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
		return CacheKey::accountPrefix('locations', $accountId);
	}

	private function key(string $accountId, string|int ...$parts): string
	{
		return CacheKey::account('locations', $accountId, ...$parts);
	}
}
