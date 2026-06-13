<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Auth;

final readonly class AuthenticatedUser
{
	public function __construct(
		public int $id,
		public string $accountId,
		public int $roleId,
		public string $name,
		public ?string $email = null,
		public ?int $locationId = null,
	) {
	}

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'account_id' => $this->accountId,
			'role_id' => $this->roleId,
			'name' => $this->name,
			'email' => $this->email,
			'location_id' => $this->locationId,
		];
	}
}
