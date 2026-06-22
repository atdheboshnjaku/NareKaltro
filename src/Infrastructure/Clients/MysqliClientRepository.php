<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Clients;

use Fin\Narekaltro\Domain\Clients\ClientFormData;
use Fin\Narekaltro\Domain\Clients\ClientProfile;
use Fin\Narekaltro\Domain\Clients\ClientRepository;
use Fin\Narekaltro\Domain\Shared\PageRequest;
use Fin\Narekaltro\Domain\Shared\PageResult;
use Fin\Narekaltro\Infrastructure\Database\Connection;
use mysqli;

final class MysqliClientRepository implements ClientRepository
{
	public function __construct(private Connection $connection)
	{
	}

	#[\Override]
	public function activeForAccount(string $accountId, string $search = ''): array
	{
		$db = $this->db();
		$sql = 'SELECT clients.id, clients.account_id, clients.location_id, clients.name,
				clients.email, clients.number, clients.country, clients.state, clients.city,
				locations.name AS location_name
			FROM Users AS clients
			LEFT JOIN Business_Locations AS locations
				ON locations.id = clients.location_id
				AND locations.account_id = clients.account_id
			WHERE clients.account_id = ?
			AND clients.role_id = 1
			AND clients.status = 1';

		if ($search === '') {
			$stmt = $db->prepare($sql . ' ORDER BY clients.name ASC');
			$stmt->bind_param('s', $accountId);
		} else {
			$sql .= ' AND (clients.name LIKE ? OR clients.email LIKE ?)';
			$like = '%' . $search . '%';
			$stmt = $db->prepare($sql . ' ORDER BY clients.name ASC');
			$stmt->bind_param('sss', $accountId, $like, $like);
		}

		$stmt->execute();
		$result = $stmt->get_result();
		$clients = [];

		while ($row = $result->fetch_assoc()) {
			$clients[] = ClientProfile::fromRow($row);
		}

		$stmt->close();

		return $clients;
	}

	#[\Override]
	public function activePageForAccount(string $accountId, PageRequest $page, string $search = ''): PageResult
	{
		$db = $this->db();
		$total = $this->activeSearchCountForAccount($accountId, $search);
		$page = $page->withinTotal($total);
		$sql = 'SELECT clients.id, clients.account_id, clients.location_id, clients.name,
				clients.email, clients.number, clients.country, clients.state, clients.city,
				locations.name AS location_name
			FROM Users AS clients
			LEFT JOIN Business_Locations AS locations
				ON locations.id = clients.location_id
				AND locations.account_id = clients.account_id
			WHERE clients.account_id = ?
			AND clients.role_id = 1
			AND clients.status = 1';
		$limit = $page->perPage;
		$offset = $page->offset();

		if ($search === '') {
			$stmt = $db->prepare($sql . ' ORDER BY clients.name ASC, clients.id ASC LIMIT ? OFFSET ?');
			$stmt->bind_param('sii', $accountId, $limit, $offset);
		} else {
			$sql .= ' AND (clients.name LIKE ? OR clients.email LIKE ?)';
			$like = '%' . $search . '%';
			$stmt = $db->prepare($sql . ' ORDER BY clients.name ASC, clients.id ASC LIMIT ? OFFSET ?');
			$stmt->bind_param('sssii', $accountId, $like, $like, $limit, $offset);
		}

		$stmt->execute();
		$result = $stmt->get_result();
		$clients = [];

		while ($row = $result->fetch_assoc()) {
			$clients[] = ClientProfile::fromRow($row);
		}

		$stmt->close();

		return new PageResult($clients, $total, $page);
	}

	#[\Override]
	public function activeCountForAccount(string $accountId): int
	{
		$db = $this->db();
		$stmt = $db->prepare(
			'SELECT COUNT(*) AS total
			FROM Users
			WHERE account_id = ?
			AND role_id = 1
			AND status = 1'
		);
		$stmt->bind_param('s', $accountId);
		$stmt->execute();

		$row = $stmt->get_result()->fetch_assoc();
		$stmt->close();

		return (int) ($row['total'] ?? 0);
	}

	private function activeSearchCountForAccount(string $accountId, string $search): int
	{
		$db = $this->db();
		$sql = 'SELECT COUNT(*) AS total
			FROM Users AS clients
			WHERE clients.account_id = ?
			AND clients.role_id = 1
			AND clients.status = 1';

		if ($search === '') {
			$stmt = $db->prepare($sql);
			$stmt->bind_param('s', $accountId);
		} else {
			$sql .= ' AND (clients.name LIKE ? OR clients.email LIKE ?)';
			$like = '%' . $search . '%';
			$stmt = $db->prepare($sql);
			$stmt->bind_param('sss', $accountId, $like, $like);
		}

		$stmt->execute();
		$row = $stmt->get_result()->fetch_assoc();
		$stmt->close();

		return (int) ($row['total'] ?? 0);
	}

	#[\Override]
	public function findActiveForAccount(int $id, string $accountId): ?ClientProfile
	{
		$db = $this->db();
		$stmt = $db->prepare(
			'SELECT clients.id, clients.account_id, clients.location_id, clients.name,
				clients.email, clients.number, clients.country, clients.state, clients.city,
				locations.name AS location_name
			FROM Users AS clients
			LEFT JOIN Business_Locations AS locations
				ON locations.id = clients.location_id
				AND locations.account_id = clients.account_id
			WHERE clients.id = ?
			AND clients.account_id = ?
			AND clients.role_id = 1
			AND clients.status = 1
			LIMIT 1'
		);
		$stmt->bind_param('is', $id, $accountId);
		$stmt->execute();

		$row = $stmt->get_result()->fetch_assoc() ?: null;
		$stmt->close();

		return $row === null ? null : ClientProfile::fromRow($row);
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
	public function create(string $accountId, ClientFormData $data): int
	{
		$db = $this->db();
		$roleId = 1;
		$locationId = $data->locationId;
		$date = date('Y-m-d H:i:s');
		$name = $data->name;
		$email = $data->email;
		$phone = $data->phone;
		$countryId = $data->countryId;
		$stateId = $data->stateId;
		$cityId = $data->cityId;
		$status = 1;
		$hash = bin2hex(random_bytes(4));
		$stmt = $db->prepare(
			'INSERT INTO Users (
				account_id, role_id, location_id, date, name, email, number,
				country, state, city, status, hash
			) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
		);
		$stmt->bind_param(
			'siissssiiiis',
			$accountId,
			$roleId,
			$locationId,
			$date,
			$name,
			$email,
			$phone,
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
	public function update(int $id, string $accountId, ClientFormData $data): void
	{
		$db = $this->db();
		$locationId = $data->locationId;
		$name = $data->name;
		$email = $data->email;
		$phone = $data->phone;
		$countryId = $data->countryId;
		$stateId = $data->stateId;
		$cityId = $data->cityId;
		$stmt = $db->prepare(
			'UPDATE Users
			SET location_id = ?, name = ?, email = ?, number = ?, country = ?, state = ?, city = ?
			WHERE id = ?
			AND account_id = ?
			AND role_id = 1
			AND status = 1'
		);
		$stmt->bind_param(
			'isssiiiis',
			$locationId,
			$name,
			$email,
			$phone,
			$countryId,
			$stateId,
			$cityId,
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
		$locationId = 0;
		$stmt = $db->prepare(
			'UPDATE Users
			SET status = ?, location_id = ?
			WHERE id = ?
			AND account_id = ?
			AND role_id = 1
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
