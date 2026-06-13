<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Clients;

use Fin\Narekaltro\Domain\Locations\BusinessLocation;
use Fin\Narekaltro\Domain\Locations\LocationRepository;
use Fin\Narekaltro\Domain\Shared\PageRequest;
use Fin\Narekaltro\Domain\Shared\PageResult;
use Fin\Narekaltro\Http\NotFoundException;

final class ClientManager
{
	public function __construct(
		private ClientRepository $clients,
		private LocationRepository $locations,
		private GeographyRepository $geography
	) {
	}

	/** @return list<ClientProfile> */
	public function list(string $accountId, string $search = ''): array
	{
		return $this->clients->activeForAccount($accountId, trim($search));
	}

	/** @return PageResult<ClientProfile> */
	public function page(string $accountId, PageRequest $page, string $search = ''): PageResult
	{
		return $this->clients->activePageForAccount($accountId, $page, trim($search));
	}

	public function count(string $accountId): int
	{
		return $this->clients->activeCountForAccount($accountId);
	}

	public function find(int $id, string $accountId): ClientProfile
	{
		return $this->clients->findActiveForAccount($id, $accountId)
			?? throw new NotFoundException('Client not found.');
	}

	public function create(string $accountId, ClientFormData $data): int
	{
		$this->assertValid($accountId, $data);

		return $this->clients->create($accountId, $data);
	}

	public function update(int $id, string $accountId, ClientFormData $data): void
	{
		$client = $this->find($id, $accountId);
		$this->assertValid($accountId, $data, $client);
		$this->clients->update($id, $accountId, $data);
	}

	public function deactivate(int $id, string $accountId): void
	{
		$this->find($id, $accountId);
		$this->clients->deactivate($id, $accountId);
	}

	/** @return list<BusinessLocation> */
	public function activeLocations(string $accountId): array
	{
		return $this->locations->activeForAccount($accountId);
	}

	/** @return list<GeographyOption> */
	public function countries(): array
	{
		return $this->geography->countries();
	}

	/** @return list<GeographyOption> */
	public function states(int $countryId): array
	{
		return $countryId > 0 ? $this->geography->statesForCountry($countryId) : [];
	}

	/** @return list<GeographyOption> */
	public function cities(int $countryId, int $stateId): array
	{
		return $countryId > 0 && $stateId > 0
			? $this->geography->citiesForState($countryId, $stateId)
			: [];
	}

	private function assertValid(
		string $accountId,
		ClientFormData $data,
		?ClientProfile $existing = null
	): void {
		$errors = $data->validate();

		$keepsExistingLocation = $existing !== null && $data->locationId === $existing->locationId;
		if (!$keepsExistingLocation && $this->locations->findActiveForAccount($data->locationId, $accountId) === null) {
			$errors['location_id'] = 'Please select an active client location.';
		}

		if (
			$data->email !== ''
			&& $this->clients->emailExists($data->email, $existing?->id)
		) {
			$errors['email'] = 'This user email already exists.';
		}

		$keepsExistingCountry = $existing !== null && $data->countryId === $existing->countryId;
		if (!$keepsExistingCountry && !$this->geography->countryExists($data->countryId)) {
			$errors['country'] = 'Please select a valid client country.';
		}

		$keepsExistingState = $existing !== null
			&& $data->countryId === $existing->countryId
			&& $data->stateId === $existing->stateId;
		if (
			$data->stateId > 0
			&& !$keepsExistingState
			&& !$this->geography->stateBelongsToCountry($data->stateId, $data->countryId)
		) {
			$errors['state'] = 'Please select a valid state or region.';
		}

		$keepsExistingCity = $existing !== null
			&& $data->countryId === $existing->countryId
			&& $data->stateId === $existing->stateId
			&& $data->cityId === $existing->cityId;
		if (
			$data->cityId > 0
			&& !$keepsExistingCity
			&& !$this->geography->cityBelongsToState($data->cityId, $data->stateId, $data->countryId)
		) {
			$errors['city'] = 'Please select a valid city.';
		}

		if ($errors !== []) {
			throw new ClientValidationFailed($errors);
		}
	}
}
