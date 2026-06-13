<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Locations;

use Fin\Narekaltro\Domain\Shared\PageRequest;
use Fin\Narekaltro\Domain\Shared\PageResult;
use Fin\Narekaltro\Http\NotFoundException;

final class LocationManager
{
	public function __construct(private LocationRepository $locations)
	{
	}

	/** @return list<BusinessLocation> */
	public function list(string $accountId): array
	{
		return $this->locations->activeForAccount($accountId);
	}

	/** @return PageResult<BusinessLocation> */
	public function page(string $accountId, PageRequest $page): PageResult
	{
		return $this->locations->activePageForAccount($accountId, $page);
	}

	public function count(string $accountId): int
	{
		return $this->locations->activeCountForAccount($accountId);
	}

	public function find(int $id, string $accountId): BusinessLocation
	{
		return $this->locations->findActiveForAccount($id, $accountId)
			?? throw new NotFoundException('Location not found.');
	}

	public function create(string $accountId, LocationFormData $data): int
	{
		$this->assertValid($accountId, $data);

		return $this->locations->create($accountId, $data);
	}

	public function update(int $id, string $accountId, LocationFormData $data): void
	{
		$this->find($id, $accountId);
		$this->assertValid($accountId, $data, $id);
		$this->locations->update($id, $accountId, $data);
	}

	public function deactivate(int $id, string $accountId): void
	{
		$location = $this->find($id, $accountId);

		if ($location->hasAssignedUsers()) {
			throw new LocationInUse();
		}

		$this->locations->deactivate($id, $accountId);
	}

	private function assertValid(string $accountId, LocationFormData $data, ?int $exceptId = null): void
	{
		$errors = $data->validate();

		if ($data->name !== '' && $this->locations->activeNameExists($accountId, $data->name, $exceptId)) {
			$errors['name'] = 'A location with this name already exists.';
		}

		if ($errors !== []) {
			throw new LocationValidationFailed($errors);
		}
	}
}
