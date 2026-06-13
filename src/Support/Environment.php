<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Support;

use Dotenv\Dotenv;

final class Environment
{
	private static array $loadedPaths = [];

	public static function load(string $basePath): void
	{
		$basePath = rtrim($basePath, DIRECTORY_SEPARATOR);

		if (isset(self::$loadedPaths[$basePath])) {
			return;
		}

		if (is_file($basePath . '/.env')) {
			Dotenv::createImmutable($basePath)->safeLoad();
		}

		self::$loadedPaths[$basePath] = true;
	}

	public static function get(string $key, ?string $default = null): ?string
	{
		$value = getenv($key);
		if ($value !== false) {
			return self::stringValue($value);
		}

		if (array_key_exists($key, $_ENV)) {
			return self::stringValue($_ENV[$key]);
		}

		if (array_key_exists($key, $_SERVER)) {
			return self::stringValue($_SERVER[$key]);
		}

		return $default;
	}

	private static function stringValue(mixed $value): ?string
	{
		return $value === null ? null : (string) $value;
	}
}
