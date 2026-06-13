<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Auth;

final readonly class PolicyProvisioningSummary
{
	public function __construct(
		public int $accountsScanned,
		public int $policiesCreated,
		public int $defaultsRepaired,
		public int $policiesUnchanged,
	) {
	}
}
