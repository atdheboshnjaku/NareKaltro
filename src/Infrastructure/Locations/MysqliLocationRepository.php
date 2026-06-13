<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Locations;

use Fin\Narekaltro\Domain\Locations\BusinessLocation;
use Fin\Narekaltro\Domain\Locations\LocationFormData;
use Fin\Narekaltro\Domain\Locations\LocationRepository;
use Fin\Narekaltro\Domain\Shared\PageRequest;
use Fin\Narekaltro\Domain\Shared\PageResult;
use Fin\Narekaltro\Infrastructure\Database\Connection;
use mysqli;

final class MysqliLocationRepository implements LocationRepository
{
	public function __construct(private Connection $connection)
	{
	}

	#[\Override]
	public function activeForAccount(string $accountId): array
	{
		$db = $this->db();
		$stmt = $db->prepare(
			'SELECT locations.id, locations.account_id, locations.name,
				COALESCE(SUM(CASE WHEN users.role_id > 1 THEN 1 ELSE 0 END), 0) AS employee_count,
				COALESCE(SUM(CASE WHEN users.role_id = 1 THEN 1 ELSE 0 END), 0) AS client_count
			FROM Business_Locations AS locations
			LEFT JOIN Users AS users
				ON users.location_id = locations.id
				AND users.account_id = locations.account_id
				AND users.status = 1
			WHERE locations.account_id = ?
			AND locations.status = 1
			GROUP BY locations.id, locations.account_id, locations.name
			ORDER BY locations.name ASC'
		);
		$stmt->bind_param('s', $accountId);
		$stmt->execute();

		$result = $stmt->get_result();
		$locations = [];

		while ($row = $result->fetch_assoc()) {
			$locations[] = BusinessLocation::fromRow($row);
		}

		$stmt->close();

		return $locations;
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
			'SELECT locations.id, locations.account_id, locations.name,
				COALESCE(SUM(CASE WHEN users.role_id > 1 THEN 1 ELSE 0 END), 0) AS employee_count,
				COALESCE(SUM(CASE WHEN users.role_id = 1 THEN 1 ELSE 0 END), 0) AS client_count
			FROM Business_Locations AS locations
			LEFT JOIN Users AS users
				ON users.location_id = locations.id
				AND users.account_id = locations.account_id
				AND users.status = 1
			WHERE locations.account_id = ?
			AND locations.status = 1
			GROUP BY locations.id, locations.account_id, locations.name
			ORDER BY locations.name ASC, locations.id ASC
			LIMIT ? OFFSET ?'
		);
		$stmt->bind_param('sii', $accountId, $limit, $offset);
		$stmt->execute();

		$result = $stmt->get_result();
		$locations = [];

		while ($row = $result->fetch_assoc()) {
			$locations[] = BusinessLocation::fromRow($row);
		}

		$stmt->close();

		return new PageResult($locations, $total, $page);
	}

	#[\Override]
	public function activeCountForAccount(string $accountId): int
	{
		$db = $this->db();
		$stmt = $db->prepare(
			'SELECT COUNT(*) AS total
			FROM Business_Locations
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
	public function findActiveForAccount(int $id, string $accountId): ?BusinessLocation
	{
		$db = $this->db();
		$stmt = $db->prepare(
			'SELECT locations.id, locations.account_id, locations.name,
				COALESCE(SUM(CASE WHEN users.role_id > 1 THEN 1 ELSE 0 END), 0) AS employee_count,
				COALESCE(SUM(CASE WHEN users.role_id = 1 THEN 1 ELSE 0 END), 0) AS client_count
			FROM Business_Locations AS locations
			LEFT JOIN Users AS users
				ON users.location_id = locations.id
				AND users.account_id = locations.account_id
				AND users.status = 1
			WHERE locations.id = ?
			AND locations.account_id = ?
			AND locations.status = 1
			GROUP BY locations.id, locations.account_id, locations.name
			LIMIT 1'
		);
		$stmt->bind_param('is', $id, $accountId);
		$stmt->execute();

		$row = $stmt->get_result()->fetch_assoc() ?: null;
		$stmt->close();

		return $row === null ? null : BusinessLocation::fromRow($row);
	}

	#[\Override]
	public function findForAccount(int $id, string $accountId): ?BusinessLocation
	{
		$db = $this->db();
		$stmt = $db->prepare(
			'SELECT locations.id, locations.account_id, locations.name,
				COALESCE(SUM(CASE WHEN users.role_id > 1 THEN 1 ELSE 0 END), 0) AS employee_count,
				COALESCE(SUM(CASE WHEN users.role_id = 1 THEN 1 ELSE 0 END), 0) AS client_count
			FROM Business_Locations AS locations
			LEFT JOIN Users AS users
				ON users.location_id = locations.id
				AND users.account_id = locations.account_id
				AND users.status = 1
			WHERE locations.id = ?
			AND locations.account_id = ?
			GROUP BY locations.id, locations.account_id, locations.name
			LIMIT 1'
		);
		$stmt->bind_param('is', $id, $accountId);
		$stmt->execute();

		$row = $stmt->get_result()->fetch_assoc() ?: null;
		$stmt->close();

		return $row === null ? null : BusinessLocation::fromRow($row);
	}

	#[\Override]
	public function activeNameExists(string $accountId, string $name, ?int $exceptId = null): bool
	{
		$db = $this->db();

		if ($exceptId === null) {
			$stmt = $db->prepare(
				'SELECT id
				FROM Business_Locations
				WHERE account_id = ?
				AND name = ?
				AND status = 1
				LIMIT 1'
			);
			$stmt->bind_param('ss', $accountId, $name);
		} else {
			$stmt = $db->prepare(
				'SELECT id
				FROM Business_Locations
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
	public function create(string $accountId, LocationFormData $data): int
	{
		$db = $this->db();
		$name = $data->name;
		$status = 1;
		$stmt = $db->prepare(
			'INSERT INTO Business_Locations (account_id, name, status)
			VALUES (?, ?, ?)'
		);
		$stmt->bind_param('ssi', $accountId, $name, $status);
		$stmt->execute();
		$stmt->close();

		return (int) $db->insert_id;
	}

	#[\Override]
	public function update(int $id, string $accountId, LocationFormData $data): void
	{
		$db = $this->db();
		$name = $data->name;
		$stmt = $db->prepare(
			'UPDATE Business_Locations
			SET name = ?
			WHERE id = ?
			AND account_id = ?
			AND status = 1'
		);
		$stmt->bind_param('sis', $name, $id, $accountId);
		$stmt->execute();
		$stmt->close();
	}

	#[\Override]
	public function deactivate(int $id, string $accountId): void
	{
		$db = $this->db();
		$status = 0;
		$stmt = $db->prepare(
			'UPDATE Business_Locations
			SET status = ?
			WHERE id = ?
			AND account_id = ?
			AND status = 1'
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
