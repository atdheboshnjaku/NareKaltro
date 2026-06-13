<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Appointments;

use Fin\Narekaltro\Domain\Appointments\AppointmentReferenceRepository;
use Fin\Narekaltro\Infrastructure\Database\Connection;

final class MysqliAppointmentReferenceRepository implements AppointmentReferenceRepository
{
	public function __construct(private Connection $connection)
	{
	}

	#[\Override]
	public function existsForAccount(int $appointmentId, string $accountId): bool
	{
		$db = $this->connection->mysqli();
		$stmt = $db->prepare(
			'SELECT appointment_id
			FROM Appointments
			WHERE appointment_id = ?
			AND account_id = ?
			LIMIT 1'
		);
		$stmt->bind_param('is', $appointmentId, $accountId);
		$stmt->execute();
		$exists = (bool) $stmt->get_result()->fetch_assoc();
		$stmt->close();

		return $exists;
	}
}
