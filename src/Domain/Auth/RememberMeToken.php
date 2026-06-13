<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Auth;

use DateTimeImmutable;

final readonly class RememberMeToken
{
	public function __construct(
		public string $selector,
		public string $validator,
		public string $plainText,
		public string $hashedValidator,
		public DateTimeImmutable $expiresAt
	) {
	}

	public static function generate(int $days = 90): self
	{
		$selector = bin2hex(random_bytes(16));
		$validator = bin2hex(random_bytes(32));

		return new self(
			selector: $selector,
			validator: $validator,
			plainText: $selector . ':' . $validator,
			hashedValidator: password_hash($validator, PASSWORD_DEFAULT),
			expiresAt: new DateTimeImmutable('+' . $days . ' days')
		);
	}

	public function expiresTimestamp(): int
	{
		return $this->expiresAt->getTimestamp();
	}

	public function expiresForDatabase(): string
	{
		return $this->expiresAt->format('Y-m-d H:i:s');
	}
}
