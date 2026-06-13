<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Services;

use Fin\Narekaltro\Domain\Shared\PageRequest;
use Fin\Narekaltro\Domain\Shared\PageResult;
use Fin\Narekaltro\Http\NotFoundException;

final class ServiceManager
{
	public function __construct(private ServiceRepository $services)
	{
	}

	/** @return list<ServiceOffering> */
	public function list(string $accountId): array
	{
		return $this->services->activeForAccount($accountId);
	}

	/** @return PageResult<ServiceOffering> */
	public function page(string $accountId, PageRequest $page): PageResult
	{
		return $this->services->activePageForAccount($accountId, $page);
	}

	public function count(string $accountId): int
	{
		return $this->services->activeCountForAccount($accountId);
	}

	public function find(int $id, string $accountId): ServiceOffering
	{
		return $this->services->findForAccount($id, $accountId)
			?? throw new NotFoundException('Service not found.');
	}

	public function create(string $accountId, ServiceFormData $data): int
	{
		$this->assertValid($accountId, $data);

		return $this->services->create($accountId, $data);
	}

	public function update(int $id, string $accountId, ServiceFormData $data): void
	{
		$this->find($id, $accountId);
		$this->assertValid($accountId, $data, $id);
		$this->services->update($id, $accountId, $data);
	}

	public function deactivate(int $id, string $accountId): void
	{
		$this->find($id, $accountId);
		$this->services->deactivate($id, $accountId);
	}

	private function assertValid(string $accountId, ServiceFormData $data, ?int $exceptId = null): void
	{
		$errors = $data->validate();

		if ($data->name !== '' && $this->services->activeNameExists($accountId, $data->name, $exceptId)) {
			$errors['name'] = 'A service with this name already exists.';
		}

		if ($errors !== []) {
			throw new ServiceValidationFailed($errors);
		}
	}
}
