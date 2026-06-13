<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Cache;

final class CacheKey
{
	private const VERSION = 'v1';

	private function __construct()
	{
	}

	public static function account(string $namespace, string $accountId, string|int ...$parts): string
	{
		return self::accountPrefix($namespace, $accountId) . self::join(...$parts);
	}

	public static function accountPrefix(string $namespace, string $accountId): string
	{
		return self::join(self::VERSION, 'account', self::hash($accountId), $namespace) . ':';
	}

	public static function global(string $namespace, string|int ...$parts): string
	{
		return self::join(self::VERSION, 'global', $namespace, ...$parts);
	}

	private static function join(string|int ...$parts): string
	{
		return implode(':', array_map(
			static fn (string|int $part): string => self::segment($part),
			$parts
		));
	}

	private static function segment(string|int $part): string
	{
		$part = strtolower(trim((string) $part));

		if ($part === '') {
			return '_';
		}

		return preg_match('/^[a-z0-9_.-]+$/', $part) === 1 ? $part : self::hash($part);
	}

	private static function hash(string $value): string
	{
		return hash('sha256', $value);
	}
}
