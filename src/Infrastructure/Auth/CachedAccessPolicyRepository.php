<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Auth;

use Fin\Narekaltro\Domain\Auth\AccessPolicyRepository;
use Fin\Narekaltro\Domain\Auth\AccountAccessPolicy;
use Fin\Narekaltro\Domain\Shared\CacheStore;
use Fin\Narekaltro\Infrastructure\Cache\CacheKey;

final class CachedAccessPolicyRepository implements AccessPolicyRepository
{
	private const TTL_SECONDS = 3600;

	public function __construct(
		private AccessPolicyRepository $inner,
		private CacheStore $cache
	) {
	}

	#[\Override]
	public function find(string $accountId): ?AccountAccessPolicy
	{
		return $this->cache->remember(
			$this->key($accountId),
			self::TTL_SECONDS,
			fn (): ?AccountAccessPolicy => $this->inner->find($accountId)
		);
	}

	#[\Override]
	public function save(AccountAccessPolicy $policy, ?int $updatedBy = null): void
	{
		$this->inner->save($policy, $updatedBy);
		$this->cache->forgetByPrefix($this->prefix($policy->accountId));
	}

	private function key(string $accountId): string
	{
		return CacheKey::account('access-policy', $accountId, 'current');
	}

	private function prefix(string $accountId): string
	{
		return CacheKey::accountPrefix('access-policy', $accountId);
	}
}
