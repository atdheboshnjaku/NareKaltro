<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Appointments;

final readonly class AppointmentScope
{
	/** @param list<int>|null $locationIds */
	private function __construct(
		public string $accountId,
		public ?array $locationIds,
		public ?int $employeeId = null
	) {
	}

	public static function account(string $accountId, ?int $employeeId = null): self
	{
		return new self($accountId, null, self::employeeId($employeeId));
	}

	/** @param list<int> $locationIds */
	public static function locations(string $accountId, array $locationIds, ?int $employeeId = null): self
	{
		$ids = [];

		foreach ($locationIds as $locationId) {
			$locationId = (int) $locationId;
			if ($locationId > 0) {
				$ids[$locationId] = $locationId;
			}
		}

		return new self($accountId, array_values($ids), self::employeeId($employeeId));
	}

	public static function none(string $accountId): self
	{
		return new self($accountId, []);
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

	public function hasEmployeeFilter(): bool
	{
		return $this->employeeId !== null;
	}

	public function includesEmployee(?int $employeeId): bool
	{
		return $this->employeeId === null || $employeeId === $this->employeeId;
	}

	private static function employeeId(?int $employeeId): ?int
	{
		return $employeeId !== null && $employeeId > 0 ? $employeeId : null;
	}
}
