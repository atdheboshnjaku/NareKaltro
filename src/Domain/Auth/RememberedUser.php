<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Auth;

final readonly class RememberedUser
{
	public function __construct(
		public AuthenticatedUser $user,
		public string $hashedValidator
	) {
	}
}
