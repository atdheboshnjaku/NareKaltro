<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Database;

use Fin\Narekaltro\Domain\Shared\TransactionManager;
use Throwable;

final class MysqliTransactionManager implements TransactionManager
{
	public function __construct(private Connection $connection)
	{
	}

	#[\Override]
	public function transactional(callable $operation): mixed
	{
		$db = $this->connection->mysqli();
		$db->begin_transaction();

		try {
			$result = $operation();
			$db->commit();

			return $result;
		} catch (Throwable $exception) {
			$db->rollback();

			throw $exception;
		}
	}
}
