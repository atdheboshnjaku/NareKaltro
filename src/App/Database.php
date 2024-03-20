<?php

declare(strict_types=1);

namespace Fin\Narekaltro\App;

use Dotenv\Dotenv;

class Database
{

	public $db = false;

	public ?string $lastQuery = null;

	public array $insertKeys = [];
	public array $insertValues = [];

	public array $updateSets = [];

	public int $id;

	public function __construct()
	{
		// Initializing a connection to the Database
		$this->connect();

	}

	public function connect()
	{
		// Using vlucas/phpdotenv package to load the environment (.env) file and call variables
		// for the database credentials
		$dotenv = Dotenv::createImmutable($_SERVER['DOCUMENT_ROOT']);
		$dotenv->load();

		// Creating a mysqli object and using the variables from withing the .env file located
		// in the root directory of the project

		$this->db = new \Mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
		$this->db->set_charset("utf8mb4");
		if ($this->db->connect_errno) {
			$msg = "Connection to the Database failed: ";
			$msg .= $this->db->connect_error;
			$msg .= " (" . $this->db->connect_errno . ")";
			exit($msg);
		}

	}

	public function closeConnection(): void
	{

		if (isset($this->db)) {
			$this->db->close();
		}

	}

	public function escape(mixed $value): mixed
	{

		if (is_string($value)) {
			return $this->db->escape_string($value);
		}
		return $value;

	}

	public function query(string $query): bool
	{

		$this->lastQuery = $query;
		$result = $this->db->query($query);
		$this->confirmQuery($result);
		return $result;

	}

	public function confirmQuery(bool $result): void
	{

		if (!$result) {
			$output = "Database query failed\n ";
			$output .= "Last SQL query: " . $this->lastQuery;
			$output .= "\n Error: " . $this->db->error;
			$output .= "\n Error number: " . $this->db->errno;
			die($output);
		} else {
			$this->db->affected_rows;
		}

	}

	public function fetchAll(string $query): array
	{

		$result = $this->db->query($query);
		$output = [];
		while ($row = $result->fetch_assoc()) {
			$output[] = $row;
		}
		$result->free();
		return $output;

	}

	public function fetchOne(string $query): array|null
	{

		$result = $this->fetchAll($query);
		return array_shift($result);

	}

	public function lastId(): int
	{

		return $this->db->insert_id;

	}

	public function prepareToInsert(array $args = null): void
	{

		// Clear previous keys and values
		$this->insertKeys = [];
		$this->insertValues = [];

		if (!empty($args)) {

			foreach ($args as $key => $value) {
				$this->insertKeys[] = $key;
				$this->insertValues[] = $this->escape($value);
			}

		}

	}

	public function insert(string $table = null): bool
	{

		if (
			!empty($table) &&
			!empty($this->insertKeys) &&
			!empty($this->insertValues)
		) {

			$sql = "INSERT INTO `{$table}` (`";
			$sql .= implode("`, `", $this->insertKeys);
			$sql .= "`) VALUES ('";
			$sql .= implode("', '", $this->insertValues);
			$sql .= "')";

			if ($this->query($sql)) {
				$this->id = $this->lastId();
				return true;
			}
			return false;

		}

	}

	public function prepareToUpdate(array $args = null)
	{

		if (!empty($args)) {
			foreach ($args as $key => $value) {
				$this->updateSets[] = "`{$key}` = '" . $this->escape($value) . "'";
			}
		}

	}

	public function update(string $table = null, int $id = null)
	{

		$columnName = $this->getTableColumnName($table);
		if (
			!empty($table) &&
			!empty($id) &&
			!empty($this->updateSets)
		) {
			$sql = "UPDATE `{$table}` SET ";
			$sql .= implode(", ", $this->updateSets);
			$sql .= " WHERE `" . $columnName['COLUMN_NAME'] . "` = '" . (int) $id . "'";
			//$sql .= " WHERE `id` = '". (int)$id ."'";
			return $this->query($sql);
		}

	}

	public function getTableColumnName(string $table): array
	{

		$sql = "SELECT COLUMN_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_NAME = '" . $this->escape($table) . "'
                AND CONSTRAINT_NAME = 'PRIMARY'";
		return $this->fetchOne($sql);

	}

	public function getAllCountries(string $table): array
	{

		$sql = "SELECT * FROM `" . $this->escape($table) . "`
                ORDER BY `name` ASC";
		return $this->fetchAll($sql);

	}

	public function getRecordsFromTableColumnValue(string $table = null, string $column = null, string $value = null): array
	{

		$sql = "SELECT * FROM `{$table}`";
		if (!empty($column)) {
			$sql .= " WHERE `{$this->escape($column)}` = '" . $this->escape($value) . "'";
		}
		$sql .= " ORDER BY ASC";
		return $this->fetchAll($sql);

	}

	public function getRecordFromTableColumnValue(string $table = null, string $column = null, string $value = null): array
	{

		$sql = "SELECT * FROM `{$table}`";
		if (!empty($column)) {
			$sql .= " WHERE `{$this->escape($column)}` = '" . $this->escape($value) . "'";
		}
		return $this->fetchOne($sql);

	}

	public function deleteRecord(string $table = null, int $id = null): bool
	{

		if (!empty($table) && !empty($id)) {
			$sql = "DELETE FROM `{$table}`
                    WHERE `id` = '" . (int) $id . "' LIMIT 1";
			return $this->query($sql);
		}

	}

	public function deactivateUser(string $table = null, int $id = null): bool
	{

		if (!empty($table) && !empty($id)) {
			$sql = "UPDATE `{$table}` SET status = '0', location_id = '0' WHERE `id` = '" . (int) $id . "'";
			return $this->query($sql);
		}

	}

	public function deactivateService(string $table = null, int $id = null): bool
	{

		if (!empty($table) && !empty($id)) {
			$sql = "UPDATE `{$table}` SET status = 0 WHERE `id` = '" . (int) $id . "'";
			return $this->query($sql);
		}

	}

	public function deactivateLocation(string $table = null, int $id = null): bool
	{

		if (!empty($table) && !empty($id)) {
			$sql = "UPDATE `{$table}` SET status = 0 WHERE `id` = '" . (int) $id . "'";
			return $this->query($sql);
		}

	}

	public function deactivateStatus(string $table = null, int $id = null): bool
	{

		$columnName = $this->getTableColumnName($table);
		if (!empty($table) && !empty($id)) {
			$sql = "UPDATE `{$table}` SET status = 0 WHERE `" . $columnName['COLUMN_NAME'] . "` = '" . (int) $id . "'";
			return $this->query($sql);
		}

	}


	public function totalCountById(string $table, int $id): array
	{

		$sql = "SELECT COUNT(*) FROM {$table}
                WHERE `location_id` = '" . (int) $id . "'
                AND `status` = 1
                AND `role_id` = 2
                OR `role_id` = 3
                OR `role_id` = 4";
		return $this->fetchOne($sql);

	}

	// Testing Dotenv
	public static function genv(): string
	{
		$dotenv = Dotenv::createImmutable("../../");
		$dotenv->load();

		return $_ENV['DB_NAME'] . " " . $_ENV['DB_PASS'];
	}



}














