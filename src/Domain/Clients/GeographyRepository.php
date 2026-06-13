<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Clients;

interface GeographyRepository
{
	/** @return list<GeographyOption> */
	public function countries(): array;

	/** @return list<GeographyOption> */
	public function statesForCountry(int $countryId): array;

	/** @return list<GeographyOption> */
	public function citiesForState(int $countryId, int $stateId): array;

	public function countryExists(int $countryId): bool;

	public function stateBelongsToCountry(int $stateId, int $countryId): bool;

	public function cityBelongsToState(int $cityId, int $stateId, int $countryId): bool;
}
