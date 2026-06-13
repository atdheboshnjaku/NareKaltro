<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Billing;

final readonly class PlanLimitCheck
{
	public function __construct(
		public PlanLimitKey $key,
		public int $used,
		public ?int $limit,
	) {
	}

	public function isUnlimited(): bool
	{
		return $this->limit === null;
	}

	public function remaining(): ?int
	{
		return $this->limit === null ? null : max(0, $this->limit - $this->used);
	}

	public function allowsAdditional(int $quantity = 1): bool
	{
		if ($this->limit === null) {
			return true;
		}

		return ($this->used + $quantity) <= $this->limit;
	}
}
