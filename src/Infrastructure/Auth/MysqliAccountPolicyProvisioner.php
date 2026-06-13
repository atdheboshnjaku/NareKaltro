<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Auth;

use Fin\Narekaltro\Domain\Auth\AccountAccessPolicy;
use Fin\Narekaltro\Domain\Auth\AccountPolicyProvisioner;
use Fin\Narekaltro\Domain\Auth\AuthenticatedUser;
use Fin\Narekaltro\Domain\Auth\Permission;
use Fin\Narekaltro\Domain\Auth\PolicyProvisioningSummary;
use Fin\Narekaltro\Domain\Auth\UserAccessFormData;
use InvalidArgumentException;
use mysqli;

final class MysqliAccountPolicyProvisioner implements AccountPolicyProvisioner
{
	public function __construct(private mysqli $db)
	{
	}

	#[\Override]
	public function provision(string $accountId, ?int $administratorId = null): bool
	{
		$accountId = trim($accountId);
		if ($accountId === '' || $accountId === '0') {
			throw new InvalidArgumentException('An account policy requires a valid account ID.');
		}

		if ($administratorId !== null && !$this->staffBelongsToAccount($administratorId, $accountId)) {
			throw new InvalidArgumentException('The initial administrator does not belong to the account.');
		}

		$policy = AccountAccessPolicy::defaults($accountId);
		if ($administratorId !== null) {
			$policy = $policy->withUserAccess(
				$administratorId,
				new UserAccessFormData(['role_admin'], [], [])
			);
		}

		$json = $policy->toJson();
		$stmt = $this->db->prepare(
			'INSERT IGNORE INTO account_access_policies (account_id, policy, revision, updated_by)
			VALUES (?, ?, 1, ?)'
		);
		$stmt->bind_param('ssi', $accountId, $json, $administratorId);
		$stmt->execute();
		$created = $stmt->affected_rows === 1;
		$stmt->close();

		return $created;
	}

	#[\Override]
	public function provisionExistingAccounts(): PolicyProvisioningSummary
	{
		$result = $this->db->query(
			"SELECT DISTINCT account_id
			FROM Users
			WHERE account_id <> ''
			AND account_id <> '0'
			ORDER BY account_id"
		);
		$accounts = $result->fetch_all(MYSQLI_ASSOC);
		$result->free();
		$created = 0;
		$repaired = 0;
		$unchanged = 0;

		foreach ($accounts as $row) {
			$accountId = (string) $row['account_id'];
			$administratorId = $this->hasActiveAdministrator($accountId)
				? null
				: $this->bootstrapAdministratorId($accountId);

			if ($this->provision($accountId, $administratorId)) {
				$created++;
				continue;
			}

			if ($this->repairUntouchedPolicyWithoutManager($accountId)) {
				$repaired++;
				continue;
			}

			$unchanged++;
		}

		return new PolicyProvisioningSummary(count($accounts), $created, $repaired, $unchanged);
	}

	private function repairUntouchedPolicyWithoutManager(string $accountId): bool
	{
		$policy = $this->findPolicy($accountId);
		if (
			$policy === null
			|| $policy->revision !== 1
			|| $this->activeStaff($accountId) === []
			|| $this->hasRequiredManager($policy)
		) {
			return false;
		}

		$administratorId = $this->bootstrapAdministratorId($accountId);
		if ($administratorId === null) {
			return false;
		}

		$candidate = $policy->withUserAccess(
			$administratorId,
			new UserAccessFormData(['role_admin'], [], [])
		);
		$json = $candidate->toJson();
		$expectedRevision = 1;
		$stmt = $this->db->prepare(
			'UPDATE account_access_policies
			SET policy = ?, revision = revision + 1, updated_by = ?
			WHERE account_id = ?
			AND revision = ?'
		);
		$stmt->bind_param('sisi', $json, $administratorId, $accountId, $expectedRevision);
		$stmt->execute();
		$repaired = $stmt->affected_rows === 1;
		$stmt->close();

		return $repaired;
	}

	private function findPolicy(string $accountId): ?AccountAccessPolicy
	{
		$stmt = $this->db->prepare(
			'SELECT policy, revision
			FROM account_access_policies
			WHERE account_id = ?
			LIMIT 1'
		);
		$stmt->bind_param('s', $accountId);
		$stmt->execute();
		$row = $stmt->get_result()->fetch_assoc() ?: null;
		$stmt->close();

		if ($row === null) {
			return null;
		}

		return AccountAccessPolicy::fromJson($accountId, (string) $row['policy'], (int) $row['revision']);
	}

	private function hasRequiredManager(AccountAccessPolicy $policy): bool
	{
		$managesUsers = false;
		$managesRoles = false;

		foreach ($this->activeStaff($policy->accountId) as $user) {
			$managesUsers = $managesUsers || $policy->allows($user, Permission::USERS_ACCESS_MANAGE);
			$managesRoles = $managesRoles || $policy->allows($user, Permission::ROLES_MANAGE);
		}

		return $managesUsers && $managesRoles;
	}

	/** @return list<AuthenticatedUser> */
	private function activeStaff(string $accountId): array
	{
		$stmt = $this->db->prepare(
			'SELECT id, account_id, role_id, location_id, name, email
			FROM Users
			WHERE account_id = ?
			AND role_id > 1
			AND status = 1'
		);
		$stmt->bind_param('s', $accountId);
		$stmt->execute();
		$result = $stmt->get_result();
		$users = [];

		while ($row = $result->fetch_assoc()) {
			$users[] = new AuthenticatedUser(
				id: (int) $row['id'],
				accountId: (string) $row['account_id'],
				roleId: (int) $row['role_id'],
				name: (string) $row['name'],
				email: $row['email'] === null ? null : (string) $row['email'],
				locationId: (int) $row['location_id']
			);
		}

		$stmt->close();

		return $users;
	}

	private function bootstrapAdministratorId(string $accountId): ?int
	{
		$stmt = $this->db->prepare(
			'SELECT id
			FROM Users
			WHERE account_id = ?
			AND role_id > 1
			ORDER BY status DESC, date ASC, id ASC
			LIMIT 1'
		);
		$stmt->bind_param('s', $accountId);
		$stmt->execute();
		$row = $stmt->get_result()->fetch_assoc() ?: null;
		$stmt->close();

		return $row === null ? null : (int) $row['id'];
	}

	private function hasActiveAdministrator(string $accountId): bool
	{
		$administratorRoleId = 4;
		$active = 1;
		$stmt = $this->db->prepare(
			'SELECT id
			FROM Users
			WHERE account_id = ?
			AND role_id = ?
			AND status = ?
			LIMIT 1'
		);
		$stmt->bind_param('sii', $accountId, $administratorRoleId, $active);
		$stmt->execute();
		$exists = (bool) $stmt->get_result()->fetch_assoc();
		$stmt->close();

		return $exists;
	}

	private function staffBelongsToAccount(int $id, string $accountId): bool
	{
		$stmt = $this->db->prepare(
			'SELECT id
			FROM Users
			WHERE id = ?
			AND account_id = ?
			AND role_id > 1
			LIMIT 1'
		);
		$stmt->bind_param('is', $id, $accountId);
		$stmt->execute();
		$exists = (bool) $stmt->get_result()->fetch_assoc();
		$stmt->close();

		return $exists;
	}
}
