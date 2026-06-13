<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Staff;

use Fin\Narekaltro\Domain\Staff\StaffFormData;
use Fin\Narekaltro\Domain\Staff\StaffMember;
use Fin\Narekaltro\Domain\Staff\StaffRepository;
use Fin\Narekaltro\Domain\Shared\PageRequest;
use Fin\Narekaltro\Domain\Shared\PageResult;
use Fin\Narekaltro\Infrastructure\Database\Connection;
use mysqli;

final class MysqliStaffRepository implements StaffRepository
{
	public function __construct(private Connection $connection)
	{
	}

	#[\Override]
	public function activeForAccount(string $accountId): array
	{
		$db = $this->db();
		$stmt = $db->prepare(
				'SELECT users.id, users.account_id, users.role_id, users.location_id, users.name, users.email,
				locations.name AS location_name
			FROM Users AS users
			LEFT JOIN Business_Locations AS locations
				ON locations.id = users.location_id
				AND locations.account_id = users.account_id
			WHERE users.account_id = ?
			AND users.status = 1
			AND users.role_id > 1
			ORDER BY users.name ASC'
		);
		$stmt->bind_param('s', $accountId);
		$stmt->execute();

		$result = $stmt->get_result();
		$staff = [];

		while ($row = $result->fetch_assoc()) {
			$staff[] = StaffMember::fromRow($row);
		}

		$stmt->close();

		return $staff;
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
				'SELECT users.id, users.account_id, users.role_id, users.location_id, users.name, users.email,
				locations.name AS location_name
			FROM Users AS users
			LEFT JOIN Business_Locations AS locations
				ON locations.id = users.location_id
				AND locations.account_id = users.account_id
			WHERE users.account_id = ?
			AND users.status = 1
			AND users.role_id > 1
			ORDER BY users.name ASC, users.id ASC
			LIMIT ? OFFSET ?'
		);
		$stmt->bind_param('sii', $accountId, $limit, $offset);
		$stmt->execute();

		$result = $stmt->get_result();
		$staff = [];

		while ($row = $result->fetch_assoc()) {
			$staff[] = StaffMember::fromRow($row);
		}

		$stmt->close();

		return new PageResult($staff, $total, $page);
	}

	#[\Override]
	public function activeCountForAccount(string $accountId): int
	{
		$db = $this->db();
		$stmt = $db->prepare(
			'SELECT COUNT(*) AS total
			FROM Users
			WHERE account_id = ?
			AND status = 1
			AND role_id > 1'
		);
		$stmt->bind_param('s', $accountId);
		$stmt->execute();

		$row = $stmt->get_result()->fetch_assoc();
		$stmt->close();

		return (int) ($row['total'] ?? 0);
	}

	#[\Override]
	public function findActiveForAccount(int $id, string $accountId): ?StaffMember
	{
		$db = $this->db();
		$stmt = $db->prepare(
				'SELECT users.id, users.account_id, users.role_id, users.location_id, users.name, users.email,
				locations.name AS location_name
			FROM Users AS users
			LEFT JOIN Business_Locations AS locations
				ON locations.id = users.location_id
				AND locations.account_id = users.account_id
			WHERE users.id = ?
			AND users.account_id = ?
			AND users.status = 1
			AND users.role_id > 1
			LIMIT 1'
		);
		$stmt->bind_param('is', $id, $accountId);
		$stmt->execute();

		$row = $stmt->get_result()->fetch_assoc() ?: null;
		$stmt->close();

		return $row === null ? null : StaffMember::fromRow($row);
	}

	#[\Override]
	public function findForAccount(int $id, string $accountId): ?StaffMember
	{
		$db = $this->db();
		$stmt = $db->prepare(
				'SELECT users.id, users.account_id, users.role_id, users.location_id, users.name, users.email,
				locations.name AS location_name
			FROM Users AS users
			LEFT JOIN Business_Locations AS locations
				ON locations.id = users.location_id
				AND locations.account_id = users.account_id
			WHERE users.id = ?
			AND users.account_id = ?
			AND users.role_id > 1
			LIMIT 1'
		);
		$stmt->bind_param('is', $id, $accountId);
		$stmt->execute();

		$row = $stmt->get_result()->fetch_assoc() ?: null;
		$stmt->close();

		return $row === null ? null : StaffMember::fromRow($row);
	}

	#[\Override]
	public function emailExists(string $email, ?int $exceptId = null): bool
	{
		$db = $this->db();

		if ($exceptId === null) {
			$stmt = $db->prepare('SELECT id FROM Users WHERE email = ? LIMIT 1');
			$stmt->bind_param('s', $email);
		} else {
			$stmt = $db->prepare('SELECT id FROM Users WHERE email = ? AND id <> ? LIMIT 1');
			$stmt->bind_param('si', $email, $exceptId);
		}

		$stmt->execute();
		$exists = (bool) $stmt->get_result()->fetch_assoc();
		$stmt->close();

		return $exists;
	}

	#[\Override]
	public function create(string $accountId, StaffFormData $data): int
	{
		$db = $this->db();
		// Dynamic access is stored in policy JSON; this baseline keeps staff rows queryable.
		$baselineRoleId = 2;
		$locationId = $data->locationId;
		$date = date('Y-m-d H:i:s');
		$name = $data->name;
		$email = $data->email;
		$phone = null;
		$password = password_hash($data->password, PASSWORD_BCRYPT, ['cost' => 12]);
		$countryId = 0;
		$stateId = 0;
		$cityId = 0;
		$status = 1;
		$hash = bin2hex(random_bytes(4));
		$stmt = $db->prepare(
			'INSERT INTO Users (
				account_id, role_id, location_id, date, name, email, number,
				password, country, state, city, status, hash
			) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
		);
		$stmt->bind_param(
			'siisssssiiiis',
			$accountId,
			$baselineRoleId,
			$locationId,
			$date,
			$name,
			$email,
			$phone,
			$password,
			$countryId,
			$stateId,
			$cityId,
			$status,
			$hash
		);
		$stmt->execute();
		$stmt->close();

		return (int) $db->insert_id;
	}

	#[\Override]
	public function update(int $id, string $accountId, StaffFormData $data): void
	{
		$db = $this->db();
		$locationId = $data->locationId;
		$name = $data->name;
		$email = $data->email;

		if ($data->password === '') {
			$stmt = $db->prepare(
				'UPDATE Users
				SET location_id = ?, name = ?, email = ?
				WHERE id = ?
				AND account_id = ?
				AND role_id > 1
				AND status = 1'
			);
			$stmt->bind_param('issis', $locationId, $name, $email, $id, $accountId);
		} else {
			$password = password_hash($data->password, PASSWORD_BCRYPT, ['cost' => 12]);
			$stmt = $db->prepare(
				'UPDATE Users
				SET location_id = ?, name = ?, email = ?, password = ?
				WHERE id = ?
				AND account_id = ?
				AND role_id > 1
				AND status = 1'
			);
			$stmt->bind_param('isssis', $locationId, $name, $email, $password, $id, $accountId);
		}

		$stmt->execute();
		$stmt->close();
	}

	#[\Override]
	public function deactivate(int $id, string $accountId): void
	{
		$db = $this->db();
		$status = 0;
		$locationId = 0;
		$stmt = $db->prepare(
			'UPDATE Users
			SET status = ?, location_id = ?
			WHERE id = ?
			AND account_id = ?
			AND role_id > 1
			AND status = 1'
		);
		$stmt->bind_param('iiis', $status, $locationId, $id, $accountId);
		$stmt->execute();
		$stmt->close();
	}

	private function db(): mysqli
	{
		return $this->connection->mysqli();
	}
}
