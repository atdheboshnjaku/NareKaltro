<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Database;

use Fin\Narekaltro\Support\Environment;
use mysqli;

final class Connection
{
	private ?mysqli $connection = null;

	private function __construct(
		private readonly string $host,
		private readonly string $username,
		private readonly string $password,
		private readonly string $database,
		private readonly ?int $port,
		private readonly ?string $socket,
	) {
	}

	public static function fromEnv(string $basePath): self
	{
		Environment::load($basePath);

		return new self(
			host: Environment::get('DB_HOST', '') ?? '',
			username: Environment::get('DB_USER', '') ?? '',
			password: Environment::get('DB_PASS', '') ?? '',
			database: Environment::get('DB_NAME', '') ?? '',
			port: self::optionalPort(Environment::get('DB_PORT')),
			socket: self::optionalString(Environment::get('DB_SOCKET')),
		);
	}

	public function mysqli(): mysqli
	{
		if ($this->connection instanceof mysqli) {
			return $this->connection;
		}

		$this->connection = new mysqli(
			$this->host,
			$this->username,
			$this->password,
			$this->database,
			$this->port,
			$this->socket,
		);
		$this->connection->set_charset('utf8mb4');

		return $this->connection;
	}

	private static function optionalPort(mixed $value): ?int
	{
		if ($value === null || $value === '') {
			return null;
		}

		return (int) $value;
	}

	private static function optionalString(mixed $value): ?string
	{
		$value = trim((string) $value);

		return $value === '' ? null : $value;
	}
}
