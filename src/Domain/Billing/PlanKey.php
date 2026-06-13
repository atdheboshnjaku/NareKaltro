<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Billing;

enum PlanKey: string
{
	case Free = 'free';
	case Pulse = 'pulse';
	case Apex = 'apex';

	public static function fromNullable(?string $value): self
	{
		if ($value === null || $value === '') {
			return self::Free;
		}

		return self::tryFrom($value) ?? self::Free;
	}
}
