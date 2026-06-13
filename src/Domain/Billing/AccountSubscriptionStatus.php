<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Billing;

enum AccountSubscriptionStatus: string
{
	case Active = 'active';
	case Trialing = 'trialing';
	case PastDue = 'past_due';
	case Cancelled = 'cancelled';

	public static function fromNullable(?string $value): self
	{
		if ($value === null || $value === '') {
			return self::Active;
		}

		return self::tryFrom($value) ?? self::Active;
	}

	public function canUsePaidEntitlements(): bool
	{
		return match ($this) {
			self::Active, self::Trialing => true,
			self::PastDue, self::Cancelled => false,
		};
	}
}
