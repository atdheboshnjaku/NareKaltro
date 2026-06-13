<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Staff;

use Fin\Narekaltro\Domain\Auth\AuthenticatedUser;

final readonly class StaffMember
{
	public function __construct(
		public int $id,
		public string $accountId,
		public int $roleId,
		public int $locationId,
		public string $name,
		public ?string $email,
		public ?string $locationName,
	) {
	}

	public static function fromRow(array $row): self
	{
		return new self(
			id: (int) $row['id'],
			accountId: (string) $row['account_id'],
			roleId: (int) $row['role_id'],
			locationId: (int) $row['location_id'],
			name: (string) $row['name'],
			email: $row['email'] === null ? null : (string) $row['email'],
			locationName: $row['location_name'] === null ? null : (string) $row['location_name'],
		);
	}

	public function authenticatedUser(): AuthenticatedUser
	{
		return new AuthenticatedUser(
			id: $this->id,
			accountId: $this->accountId,
			roleId: $this->roleId,
			name: $this->name,
			email: $this->email,
			locationId: $this->locationId
		);
	}
}
