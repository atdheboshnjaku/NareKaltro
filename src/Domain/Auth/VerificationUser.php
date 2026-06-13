<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Auth;

final readonly class VerificationUser
{
	public function __construct(
		public int $id,
		public string $email,
		public string $hash,
		public bool $active
	) {
	}
}
