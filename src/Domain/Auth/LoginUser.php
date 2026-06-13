<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Auth;

final readonly class LoginUser
{
	public function __construct(
		public AuthenticatedUser $user,
		public string $passwordHash
	) {
	}
}
