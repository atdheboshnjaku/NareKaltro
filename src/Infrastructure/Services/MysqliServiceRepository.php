<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Services;

use Fin\Narekaltro\Domain\Services\ServiceFormData;
use Fin\Narekaltro\Domain\Services\ServiceOffering;
use Fin\Narekaltro\Domain\Services\ServiceRepository;
use Fin\Narekaltro\Domain\Shared\PageRequest;
use Fin\Narekaltro\Domain\Shared\PageResult;
use Fin\Narekaltro\Infrastructure\Database\Connection;
use mysqli;

final class MysqliServiceRepository implements ServiceRepository
{
	public function __construct(private Connection $connection)
	{
	}

	#[\Override]
	public function activeForAccount(string $accountId): array
	{
		$db = $this->db();
		$stmt = $db->prepare(
			'SELECT id, account_id, name, background, color, status
			FROM Services
			WHERE account_id = ?
			AND status = 1
			ORDER BY name ASC'
		);
		$stmt->bind_param('s', $accountId);
		$stmt->execute();

		$result = $stmt->get_result();
		$services = [];

		while ($row = $result->fetch_assoc()) {
			$services[] = ServiceOffering::fromRow($row);
		}

		$stmt->close();

		return $services;
	}

	#[\Override]
	public function activePageForAccount(string $accountId, PageRequest $page): PageResult
	{
		$db = $this->db();
		$total = $this->activeCountForAccount($accountId);
		$page = $page->withinTotal($total);
		$limit = $page->perPage;
		$offset = $page->offset();
		$stmt = $db->prepare(
			'SELECT id, account_id, name, background, color, status
			FROM Services
			WHERE account_id = ?
			AND status = 1
			ORDER BY name ASC, id ASC
			LIMIT ? OFFSET ?'
		);
		$stmt->bind_param('sii', $accountId, $limit, $offset);
		$stmt->execute();

		$result = $stmt->get_result();
		$services = [];

		while ($row = $result->fetch_assoc()) {
			$services[] = ServiceOffering::fromRow($row);
		}

		$stmt->close();

		return new PageResult($services, $total, $page);
	}

	#[\Override]
	public function activeCountForAccount(string $accountId): int
	{
		$db = $this->db();
		$stmt = $db->prepare(
			'SELECT COUNT(*) AS total
			FROM Services
			WHERE account_id = ?
			AND status = 1'
		);
		$stmt->bind_param('s', $accountId);
		$stmt->execute();

		$row = $stmt->get_result()->fetch_assoc();
		$stmt->close();

		return (int) ($row['total'] ?? 0);
	}

	#[\Override]
	public function findForAccount(int $id, string $accountId): ?ServiceOffering
	{
		$db = $this->db();
		$stmt = $db->prepare(
			'SELECT id, account_id, name, background, color, status
			FROM Services
			WHERE id = ?
			AND account_id = ?
			LIMIT 1'
		);
		$stmt->bind_param('is', $id, $accountId);
		$stmt->execute();

		$row = $stmt->get_result()->fetch_assoc() ?: null;
		$stmt->close();

		return $row === null ? null : ServiceOffering::fromRow($row);
	}

	#[\Override]
	public function activeNameExists(string $accountId, string $name, ?int $exceptId = null): bool
	{
		$db = $this->db();

		if ($exceptId === null) {
			$stmt = $db->prepare(
				'SELECT id
				FROM Services
				WHERE account_id = ?
				AND name = ?
				AND status = 1
				LIMIT 1'
			);
			$stmt->bind_param('ss', $accountId, $name);
		} else {
			$stmt = $db->prepare(
				'SELECT id
				FROM Services
				WHERE account_id = ?
				AND name = ?
				AND status = 1
				AND id <> ?
				LIMIT 1'
			);
			$stmt->bind_param('ssi', $accountId, $name, $exceptId);
		}

		$stmt->execute();
		$exists = (bool) $stmt->get_result()->fetch_assoc();
		$stmt->close();

		return $exists;
	}

	#[\Override]
	public function create(string $accountId, ServiceFormData $data): int
	{
		$db = $this->db();
		$row = $data->toDatabaseRow();
		$status = 1;
		$stmt = $db->prepare(
			'INSERT INTO Services (account_id, name, background, color, status)
			VALUES (?, ?, ?, ?, ?)'
		);
		$stmt->bind_param(
			'ssssi',
			$accountId,
			$row['name'],
			$row['background'],
			$row['color'],
			$status
		);
		$stmt->execute();
		$stmt->close();

		return (int) $db->insert_id;
	}

	#[\Override]
	public function update(int $id, string $accountId, ServiceFormData $data): void
	{
		$db = $this->db();
		$row = $data->toDatabaseRow();
		$stmt = $db->prepare(
			'UPDATE Services
			SET name = ?, background = ?, color = ?
			WHERE id = ?
			AND account_id = ?'
		);
		$stmt->bind_param(
			'sssis',
			$row['name'],
			$row['background'],
			$row['color'],
			$id,
			$accountId
		);
		$stmt->execute();
		$stmt->close();
	}

	#[\Override]
	public function deactivate(int $id, string $accountId): void
	{
		$db = $this->db();
		$status = 0;
		$stmt = $db->prepare(
			'UPDATE Services
			SET status = ?
			WHERE id = ?
			AND account_id = ?'
		);
		$stmt->bind_param('iis', $status, $id, $accountId);
		$stmt->execute();
		$stmt->close();
	}

	private function db(): mysqli
	{
		return $this->connection->mysqli();
	}
}
