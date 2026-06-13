<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Clients;

use Fin\Narekaltro\Domain\Clients\GeographyOption;
use Fin\Narekaltro\Domain\Clients\GeographyRepository;
use Fin\Narekaltro\Infrastructure\Database\Connection;
use mysqli;

final class MysqliGeographyRepository implements GeographyRepository
{
	public function __construct(private Connection $connection)
	{
	}

	#[\Override]
	public function countries(): array
	{
		return $this->options('SELECT id, name FROM Countries ORDER BY name ASC');
	}

	#[\Override]
	public function statesForCountry(int $countryId): array
	{
		$db = $this->db();
		$stmt = $db->prepare('SELECT id, name FROM States WHERE country_id = ? ORDER BY name ASC');
		$stmt->bind_param('i', $countryId);

		return $this->statementOptions($stmt);
	}

	#[\Override]
	public function citiesForState(int $countryId, int $stateId): array
	{
		$db = $this->db();
		$stmt = $db->prepare(
			'SELECT id, name
			FROM Cities
			WHERE country_id = ?
			AND state_id = ?
			ORDER BY name ASC'
		);
		$stmt->bind_param('ii', $countryId, $stateId);

		return $this->statementOptions($stmt);
	}

	#[\Override]
	public function countryExists(int $countryId): bool
	{
		$stmt = $this->db()->prepare('SELECT id FROM Countries WHERE id = ? LIMIT 1');
		$stmt->bind_param('i', $countryId);

		return $this->hasRow($stmt);
	}

	#[\Override]
	public function stateBelongsToCountry(int $stateId, int $countryId): bool
	{
		$stmt = $this->db()->prepare(
			'SELECT id FROM States WHERE id = ? AND country_id = ? LIMIT 1'
		);
		$stmt->bind_param('ii', $stateId, $countryId);

		return $this->hasRow($stmt);
	}

	#[\Override]
	public function cityBelongsToState(int $cityId, int $stateId, int $countryId): bool
	{
		$stmt = $this->db()->prepare(
			'SELECT id FROM Cities WHERE id = ? AND state_id = ? AND country_id = ? LIMIT 1'
		);
		$stmt->bind_param('iii', $cityId, $stateId, $countryId);

		return $this->hasRow($stmt);
	}

	/** @return list<GeographyOption> */
	private function options(string $query): array
	{
		$result = $this->db()->query($query);
		$options = [];

		while ($row = $result->fetch_assoc()) {
			$options[] = GeographyOption::fromRow($row);
		}

		$result->free();

		return $options;
	}

	/** @return list<GeographyOption> */
	private function statementOptions(\mysqli_stmt $stmt): array
	{
		$stmt->execute();
		$result = $stmt->get_result();
		$options = [];

		while ($row = $result->fetch_assoc()) {
			$options[] = GeographyOption::fromRow($row);
		}

		$stmt->close();

		return $options;
	}

	private function hasRow(\mysqli_stmt $stmt): bool
	{
		$stmt->execute();
		$exists = (bool) $stmt->get_result()->fetch_assoc();
		$stmt->close();

		return $exists;
	}

	private function db(): mysqli
	{
		return $this->connection->mysqli();
	}
}
