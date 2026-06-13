<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Auth;

use Fin\Narekaltro\Domain\Auth\AccessPolicyRepository;
use Fin\Narekaltro\Domain\Auth\AccountAccessPolicy;
use Fin\Narekaltro\Infrastructure\Database\Connection;

final class MysqliAccessPolicyRepository implements AccessPolicyRepository
{
	/** @var array<string, AccountAccessPolicy|null> */
	private array $loaded = [];

	public function __construct(private Connection $connection)
	{
	}

	#[\Override]
	public function find(string $accountId): ?AccountAccessPolicy
	{
		if (array_key_exists($accountId, $this->loaded)) {
			return $this->loaded[$accountId];
		}

		$db = $this->connection->mysqli();
		$stmt = $db->prepare(
			'SELECT account_id, policy, revision
			FROM account_access_policies
			WHERE account_id = ?
			LIMIT 1'
		);
		$stmt->bind_param('s', $accountId);
		$stmt->execute();

		$row = $stmt->get_result()->fetch_assoc() ?: null;
		$stmt->close();

		if ($row === null) {
			return $this->loaded[$accountId] = null;
		}

		return $this->loaded[$accountId] = AccountAccessPolicy::fromJson(
			(string) $row['account_id'],
			(string) $row['policy'],
			(int) $row['revision']
		);
	}

	#[\Override]
	public function save(AccountAccessPolicy $policy, ?int $updatedBy = null): void
	{
		$db = $this->connection->mysqli();
		$accountId = $policy->accountId;
		$json = $policy->toJson();
		$stmt = $db->prepare(
			'INSERT INTO account_access_policies (account_id, policy, revision, updated_by)
			VALUES (?, ?, 1, ?)
			ON DUPLICATE KEY UPDATE
				policy = VALUES(policy),
				revision = revision + 1,
				updated_by = VALUES(updated_by)'
		);
		$stmt->bind_param('ssi', $accountId, $json, $updatedBy);
		$stmt->execute();
		$stmt->close();

		$this->loaded[$accountId] = $policy;
	}
}
