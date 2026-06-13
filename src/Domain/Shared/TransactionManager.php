<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Shared;

interface TransactionManager
{
	public function transactional(callable $operation): mixed;
}
