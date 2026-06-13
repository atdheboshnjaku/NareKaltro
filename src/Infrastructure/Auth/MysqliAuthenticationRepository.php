<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Auth;

use Fin\Narekaltro\Domain\Auth\AuthenticatedUser;
use Fin\Narekaltro\Domain\Auth\AuthenticationRepository;
use Fin\Narekaltro\Domain\Auth\LoginUser;
use Fin\Narekaltro\Domain\Auth\RememberedUser;
use Fin\Narekaltro\Domain\Auth\RememberMeToken;
use Fin\Narekaltro\Domain\Auth\VerificationUser;
use Fin\Narekaltro\Infrastructure\Database\Connection;
use mysqli;

final class MysqliAuthenticationRepository implements AuthenticationRepository
{
	public function __construct(private Connection $connection)
	{
	}

	#[\Override]
	public function findActiveUserById(int $id): ?AuthenticatedUser
	{
		$stmt = $this->db()->prepare(
			'SELECT id, account_id, role_id, location_id, name, email
			FROM Users
			WHERE id = ?
			AND status = 1
			LIMIT 1'
		);
		$stmt->bind_param('i', $id);
		$stmt->execute();
		$row = $stmt->get_result()->fetch_assoc() ?: null;
		$stmt->close();

		return $row === null ? null : $this->authenticatedUser($row);
	}

	#[\Override]
	public function findActiveUserByEmail(string $email): ?LoginUser
	{
		$email = trim($email);
		$stmt = $this->db()->prepare(
			'SELECT id, account_id, role_id, location_id, name, email, password
			FROM Users
			WHERE email = ?
			AND status = 1
			LIMIT 1'
		);
		$stmt->bind_param('s', $email);
		$stmt->execute();
		$row = $stmt->get_result()->fetch_assoc() ?: null;
		$stmt->close();

		if ($row === null) {
			return null;
		}

		return new LoginUser(
			user: $this->authenticatedUser($row),
			passwordHash: (string) ($row['password'] ?? '')
		);
	}

	#[\Override]
	public function findActiveLoginUserById(int $id): ?LoginUser
	{
		$stmt = $this->db()->prepare(
			'SELECT id, account_id, role_id, location_id, name, email, password
			FROM Users
			WHERE id = ?
			AND status = 1
			LIMIT 1'
		);
		$stmt->bind_param('i', $id);
		$stmt->execute();
		$row = $stmt->get_result()->fetch_assoc() ?: null;
		$stmt->close();

		if ($row === null) {
			return null;
		}

		return new LoginUser(
			user: $this->authenticatedUser($row),
			passwordHash: (string) ($row['password'] ?? '')
		);
	}

	#[\Override]
	public function findRememberedUser(string $selector): ?RememberedUser
	{
		$stmt = $this->db()->prepare(
			'SELECT users.id, users.account_id, users.role_id, users.location_id, users.name, users.email,
				tokens.hashed_validator
			FROM User_Tokens AS tokens
			INNER JOIN Users AS users
				ON users.id = tokens.user_id
			WHERE tokens.selector = ?
			AND tokens.expiry > NOW()
			AND users.status = 1
			LIMIT 1'
		);
		$stmt->bind_param('s', $selector);
		$stmt->execute();
		$row = $stmt->get_result()->fetch_assoc() ?: null;
		$stmt->close();

		if ($row === null) {
			return null;
		}

		return new RememberedUser(
			user: $this->authenticatedUser($row),
			hashedValidator: (string) $row['hashed_validator']
		);
	}

	#[\Override]
	public function replaceRememberToken(int $userId, RememberMeToken $token): void
	{
		$this->deleteRememberTokens($userId);

		$expiresAt = $token->expiresForDatabase();
		$selector = $token->selector;
		$hashedValidator = $token->hashedValidator;
		$stmt = $this->db()->prepare(
			'INSERT INTO User_Tokens (user_id, selector, hashed_validator, expiry)
			VALUES (?, ?, ?, ?)'
		);
		$stmt->bind_param('isss', $userId, $selector, $hashedValidator, $expiresAt);
		$stmt->execute();
		$stmt->close();
	}

	#[\Override]
	public function deleteRememberTokens(int $userId): void
	{
		$stmt = $this->db()->prepare('DELETE FROM User_Tokens WHERE user_id = ?');
		$stmt->bind_param('i', $userId);
		$stmt->execute();
		$stmt->close();
	}

	#[\Override]
	public function emailExists(string $email): bool
	{
		$email = trim($email);
		$stmt = $this->db()->prepare('SELECT id FROM Users WHERE email = ? LIMIT 1');
		$stmt->bind_param('s', $email);
		$stmt->execute();
		$exists = $stmt->get_result()->fetch_assoc() !== null;
		$stmt->close();

		return $exists;
	}

	#[\Override]
	public function activeEmailExists(string $email): bool
	{
		$email = trim($email);
		$stmt = $this->db()->prepare(
			'SELECT id
			FROM Users
			WHERE email = ?
			AND status = 1
			LIMIT 1'
		);
		$stmt->bind_param('s', $email);
		$stmt->execute();
		$exists = $stmt->get_result()->fetch_assoc() !== null;
		$stmt->close();

		return $exists;
	}

	#[\Override]
	public function createPendingAccount(string $accountId, string $email, string $hash): int
	{
		$roleId = 2;
		$locationId = 0;
		$createdAt = date('Y-m-d H:i:s');
		$countryId = 0;
		$status = 0;
		$stmt = $this->db()->prepare(
			'INSERT INTO Users (account_id, role_id, location_id, date, country, hash, email, status)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
		);
		$stmt->bind_param(
			'siisissi',
			$accountId,
			$roleId,
			$locationId,
			$createdAt,
			$countryId,
			$hash,
			$email,
			$status
		);
		$stmt->execute();
		$id = $stmt->insert_id;
		$stmt->close();

		return (int) $id;
	}

	#[\Override]
	public function findVerificationUser(string $hash): ?VerificationUser
	{
		$stmt = $this->db()->prepare(
			'SELECT id, email, status
			FROM Users
			WHERE hash = ?
			LIMIT 1'
		);
		$stmt->bind_param('s', $hash);
		$stmt->execute();
		$row = $stmt->get_result()->fetch_assoc() ?: null;
		$stmt->close();

		if ($row === null) {
			return null;
		}

		return new VerificationUser(
			id: (int) $row['id'],
			email: (string) $row['email'],
			hash: $hash,
			active: (int) $row['status'] === 1
		);
	}

	#[\Override]
	public function findPendingVerificationByEmail(string $email): ?VerificationUser
	{
		$email = trim($email);
		$stmt = $this->db()->prepare(
			"SELECT id, email, hash
			FROM Users
			WHERE email = ?
			AND status = 0
			AND hash <> ''
			LIMIT 1"
		);
		$stmt->bind_param('s', $email);
		$stmt->execute();
		$row = $stmt->get_result()->fetch_assoc() ?: null;
		$stmt->close();

		if ($row === null) {
			return null;
		}

		return new VerificationUser(
			id: (int) $row['id'],
			email: (string) $row['email'],
			hash: (string) $row['hash'],
			active: false
		);
	}

	#[\Override]
	public function completeVerification(int $userId, string $hash, string $name, string $passwordHash): bool
	{
		$status = 1;
		$clearedHash = '';
		$stmt = $this->db()->prepare(
			'UPDATE Users
			SET name = ?, password = ?, status = ?, hash = ?
			WHERE id = ?
			AND hash = ?
			AND status = 0'
		);
		$stmt->bind_param('ssisis', $name, $passwordHash, $status, $clearedHash, $userId, $hash);
		$stmt->execute();
		$updated = $stmt->affected_rows === 1;
		$stmt->close();

		return $updated;
	}

	#[\Override]
	public function updatePassword(int $userId, string $passwordHash): bool
	{
		$stmt = $this->db()->prepare(
			'UPDATE Users
			SET password = ?
			WHERE id = ?
			AND status = 1'
		);
		$stmt->bind_param('si', $passwordHash, $userId);
		$stmt->execute();
		$updated = $stmt->affected_rows === 1;
		$stmt->close();

		return $updated;
	}

	private function authenticatedUser(array $row): AuthenticatedUser
	{
		return new AuthenticatedUser(
			id: (int) $row['id'],
			accountId: (string) $row['account_id'],
			roleId: (int) $row['role_id'],
			name: (string) ($row['name'] ?? ''),
			email: $row['email'] === null ? null : (string) $row['email'],
			locationId: $row['location_id'] === null ? null : (int) $row['location_id']
		);
	}

	private function db(): mysqli
	{
		return $this->connection->mysqli();
	}
}
