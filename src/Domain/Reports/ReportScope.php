<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Reports;

final readonly class ReportScope
{
	/** @param list<int>|null $locationIds */
	private function __construct(
		public string $accountId,
		public ?array $locationIds,
		public bool $canViewValues,
		public ?int $locationFilterId = null,
		public ?int $employeeFilterId = null
	) {
	}

	public static function account(string $accountId, bool $canViewValues): self
	{
		return new self($accountId, null, $canViewValues);
	}

	/** @param list<int> $locationIds */
	public static function locations(string $accountId, array $locationIds, bool $canViewValues): self
	{
		$ids = [];

		foreach ($locationIds as $locationId) {
			$locationId = (int) $locationId;
			if ($locationId > 0) {
				$ids[$locationId] = $locationId;
			}
		}

		return new self($accountId, array_values($ids), $canViewValues);
	}

	public static function none(string $accountId): self
	{
		return new self($accountId, [], false);
	}

	public function isAccountWide(): bool
	{
		return $this->locationIds === null;
	}

	public function hasVisibleLocations(): bool
	{
		return $this->isAccountWide() || $this->locationIds !== [];
	}

	public function includesLocation(int $locationId): bool
	{
		if ($this->isAccountWide()) {
			return true;
		}

		return in_array($locationId, $this->locationIds, true);
	}

	#[\NoDiscard]
	public function withFilters(ReportFilters $filters): self
	{
		return clone($this, [
			'locationFilterId' => $filters->locationId,
			'employeeFilterId' => $filters->employeeId,
		]);
	}

	public function hasEmployeeFilter(): bool
	{
		return $this->employeeFilterId !== null;
	}

	public function effectiveLocationIds(): ?array
	{
		if ($this->locationFilterId !== null) {
			return $this->includesLocation($this->locationFilterId) ? [$this->locationFilterId] : [];
		}

		return $this->locationIds;
	}
}
