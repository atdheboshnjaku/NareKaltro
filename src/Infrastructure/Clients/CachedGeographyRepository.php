<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Clients;

use Fin\Narekaltro\Domain\Clients\GeographyRepository;
use Fin\Narekaltro\Domain\Shared\CacheStore;
use Fin\Narekaltro\Infrastructure\Cache\CacheKey;

final class CachedGeographyRepository implements GeographyRepository
{
	private const TTL_SECONDS = 86400;

	public function __construct(
		private GeographyRepository $inner,
		private CacheStore $cache
	) {
	}

	#[\Override]
	public function countries(): array
	{
		return $this->cache->remember(
			CacheKey::global('geography', 'countries'),
			self::TTL_SECONDS,
			fn (): array => $this->inner->countries()
		);
	}

	#[\Override]
	public function statesForCountry(int $countryId): array
	{
		return $this->cache->remember(
			CacheKey::global('geography', 'states', $countryId),
			self::TTL_SECONDS,
			fn (): array => $this->inner->statesForCountry($countryId)
		);
	}

	#[\Override]
	public function citiesForState(int $countryId, int $stateId): array
	{
		return $this->cache->remember(
			CacheKey::global('geography', 'cities', $countryId, $stateId),
			self::TTL_SECONDS,
			fn (): array => $this->inner->citiesForState($countryId, $stateId)
		);
	}

	#[\Override]
	public function countryExists(int $countryId): bool
	{
		return $this->cache->remember(
			CacheKey::global('geography', 'country-exists', $countryId),
			self::TTL_SECONDS,
			fn (): bool => $this->inner->countryExists($countryId)
		);
	}

	#[\Override]
	public function stateBelongsToCountry(int $stateId, int $countryId): bool
	{
		return $this->cache->remember(
			CacheKey::global('geography', 'state-belongs', $countryId, $stateId),
			self::TTL_SECONDS,
			fn (): bool => $this->inner->stateBelongsToCountry($stateId, $countryId)
		);
	}

	#[\Override]
	public function cityBelongsToState(int $cityId, int $stateId, int $countryId): bool
	{
		return $this->cache->remember(
			CacheKey::global('geography', 'city-belongs', $countryId, $stateId, $cityId),
			self::TTL_SECONDS,
			fn (): bool => $this->inner->cityBelongsToState($cityId, $stateId, $countryId)
		);
	}
}
