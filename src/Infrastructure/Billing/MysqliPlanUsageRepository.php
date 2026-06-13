<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Billing;

use DateTimeImmutable;
use Fin\Narekaltro\Domain\Billing\PlanUsageRepository;
use Fin\Narekaltro\Domain\Billing\PlanUsageSnapshot;
use Fin\Narekaltro\Infrastructure\Database\Connection;
use mysqli;

final class MysqliPlanUsageRepository implements PlanUsageRepository
{
	private ?bool $appointmentsHaveDateAdded = null;

	public function __construct(private Connection $connection)
	{
	}

	#[\Override]
	public function forAccount(string $accountId, DateTimeImmutable $month): PlanUsageSnapshot
	{
		$monthStart = $month->modify('first day of this month')->setTime(0, 0);
		$nextMonth = $monthStart->modify('first day of next month');

		return new PlanUsageSnapshot(
			activeLocations: $this->activeLocationCount($accountId),
			activeStaffMembers: $this->activeStaffCount($accountId),
			bookingsThisMonth: $this->bookingCountForMonth($accountId, $monthStart, $nextMonth),
		);
	}

	private function activeLocationCount(string $accountId): int
	{
		return $this->scalarCount(
			'SELECT COUNT(*) AS total FROM Business_Locations WHERE account_id = ? AND status = 1',
			$accountId
		);
	}

	private function activeStaffCount(string $accountId): int
	{
		return $this->scalarCount(
			'SELECT COUNT(*) AS total FROM Users WHERE account_id = ? AND role_id > 1 AND status = 1',
			$accountId
		);
	}

	private function bookingCountForMonth(string $accountId, DateTimeImmutable $from, DateTimeImmutable $until): int
	{
		$dateColumn = $this->appointmentsHaveDateAdded() ? 'COALESCE(date_added, start_date)' : 'start_date';
		$fromDate = $from->format('Y-m-d H:i:s');
		$untilDate = $until->format('Y-m-d H:i:s');
		$stmt = $this->db()->prepare(
			"SELECT COUNT(*) AS total
			FROM Appointments
			WHERE account_id = ?
			AND {$dateColumn} >= ?
			AND {$dateColumn} < ?"
		);
		$stmt->bind_param('sss', $accountId, $fromDate, $untilDate);
		$stmt->execute();
		$row = $stmt->get_result()->fetch_assoc();
		$stmt->close();

		return (int) ($row['total'] ?? 0);
	}

	private function scalarCount(string $sql, string $accountId): int
	{
		$stmt = $this->db()->prepare($sql);
		$stmt->bind_param('s', $accountId);
		$stmt->execute();
		$row = $stmt->get_result()->fetch_assoc();
		$stmt->close();

		return (int) ($row['total'] ?? 0);
	}

	private function appointmentsHaveDateAdded(): bool
	{
		if ($this->appointmentsHaveDateAdded !== null) {
			return $this->appointmentsHaveDateAdded;
		}

		$result = $this->db()->query("SHOW COLUMNS FROM Appointments LIKE 'date_added'");
		$exists = $result !== false && $result->num_rows > 0;
		if ($result instanceof \mysqli_result) {
			$result->free();
		}

		return $this->appointmentsHaveDateAdded = $exists;
	}

	private function db(): mysqli
	{
		return $this->connection->mysqli();
	}
}
