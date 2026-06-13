<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Locations;

final readonly class BusinessLocation
{
	public function __construct(
		public int $id,
		public string $accountId,
		public string $name,
		public int $employeeCount,
		public int $clientCount,
	) {
	}

	public static function fromRow(array $row): self
	{
		return new self(
			id: (int) $row['id'],
			accountId: (string) $row['account_id'],
			name: (string) $row['name'],
			employeeCount: (int) ($row['employee_count'] ?? 0),
			clientCount: (int) ($row['client_count'] ?? 0),
		);
	}

	public function hasAssignedUsers(): bool
	{
		return $this->employeeCount + $this->clientCount > 0;
	}
}
