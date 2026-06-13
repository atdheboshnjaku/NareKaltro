<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Billing;

use DateTimeImmutable;
use DateTimeInterface;
use Fin\Narekaltro\Domain\Billing\AccountSubscription;
use Fin\Narekaltro\Domain\Billing\AccountSubscriptionRepository;
use Fin\Narekaltro\Domain\Billing\AccountSubscriptionStatus;
use Fin\Narekaltro\Domain\Billing\PlanKey;
use Fin\Narekaltro\Infrastructure\Database\Connection;
use mysqli;

final class MysqliAccountSubscriptionRepository implements AccountSubscriptionRepository
{
	public function __construct(private Connection $connection)
	{
	}

	#[\Override]
	public function forAccount(string $accountId): AccountSubscription
	{
		$stmt = $this->db()->prepare(
			'SELECT account_id, plan_key, status, trial_ends_at, current_period_starts_at,
				current_period_ends_at, created_at, updated_at
			FROM account_subscriptions
			WHERE account_id = ?
			LIMIT 1'
		);
		$stmt->bind_param('s', $accountId);
		$stmt->execute();
		$row = $stmt->get_result()->fetch_assoc() ?: null;
		$stmt->close();

		return $row === null ? AccountSubscription::defaultForAccount($accountId) : $this->fromRow($row);
	}

	#[\Override]
	public function save(AccountSubscription $subscription): void
	{
		$accountId = $subscription->accountId;
		$planKey = $subscription->planKey->value;
		$status = $subscription->status->value;
		$trialEndsAt = $this->formatDate($subscription->trialEndsAt);
		$currentPeriodStartsAt = $this->formatDate($subscription->currentPeriodStartsAt);
		$currentPeriodEndsAt = $this->formatDate($subscription->currentPeriodEndsAt);
		$stmt = $this->db()->prepare(
			'INSERT INTO account_subscriptions (
				account_id, plan_key, status, trial_ends_at, current_period_starts_at, current_period_ends_at, created_at, updated_at
			) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
			ON DUPLICATE KEY UPDATE
				plan_key = VALUES(plan_key),
				status = VALUES(status),
				trial_ends_at = VALUES(trial_ends_at),
				current_period_starts_at = VALUES(current_period_starts_at),
				current_period_ends_at = VALUES(current_period_ends_at),
				updated_at = NOW()'
		);
		$stmt->bind_param(
			'ssssss',
			$accountId,
			$planKey,
			$status,
			$trialEndsAt,
			$currentPeriodStartsAt,
			$currentPeriodEndsAt
		);
		$stmt->execute();
		$stmt->close();
	}

	/** @param array<string, mixed> $row */
	private function fromRow(array $row): AccountSubscription
	{
		return new AccountSubscription(
			accountId: (string) $row['account_id'],
			planKey: PlanKey::fromNullable($row['plan_key'] === null ? null : (string) $row['plan_key']),
			status: AccountSubscriptionStatus::fromNullable($row['status'] === null ? null : (string) $row['status']),
			trialEndsAt: $this->parseDate($row['trial_ends_at'] ?? null),
			currentPeriodStartsAt: $this->parseDate($row['current_period_starts_at'] ?? null),
			currentPeriodEndsAt: $this->parseDate($row['current_period_ends_at'] ?? null),
			createdAt: $this->parseDate($row['created_at'] ?? null),
			updatedAt: $this->parseDate($row['updated_at'] ?? null),
		);
	}

	private function parseDate(mixed $value): ?DateTimeImmutable
	{
		if (!is_string($value) || trim($value) === '') {
			return null;
		}

		return new DateTimeImmutable($value);
	}

	private function formatDate(?DateTimeInterface $date): ?string
	{
		return $date?->format('Y-m-d H:i:s');
	}

	private function db(): mysqli
	{
		return $this->connection->mysqli();
	}
}
