<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Cache;

use Fin\Narekaltro\Domain\Shared\CacheStore;
use Fin\Narekaltro\Support\Environment;

final class CacheStoreFactory
{
	public static function fromEnv(string $basePath): CacheStore
	{
		Environment::load($basePath);

		$driver = strtolower(Environment::get('CACHE_DRIVER', 'file') ?? 'file');

		if ($driver === 'array') {
			return new ArrayCacheStore();
		}

		if ($driver === 'redis') {
			$redis = self::redis();
			if ($redis !== null) {
				return new RedisCacheStore(
					$redis,
					Environment::get('CACHE_PREFIX', 'narekaltro:') ?? 'narekaltro:'
				);
			}
		}

		return new FileCacheStore(sys_get_temp_dir() . '/narekaltro/cache');
	}

	private static function redis(): ?\Redis
	{
		if (!class_exists(\Redis::class)) {
			return null;
		}

		$redis = new \Redis();
		$host = Environment::get('REDIS_HOST', '127.0.0.1') ?? '127.0.0.1';
		$port = (int) (Environment::get('REDIS_PORT', '6379') ?? '6379');
		$timeout = (float) (Environment::get('REDIS_TIMEOUT', '1.0') ?? '1.0');

		if (!@$redis->connect($host, $port, $timeout)) {
			return null;
		}

		$password = Environment::get('REDIS_PASSWORD', '') ?? '';
		if ($password !== '' && !@$redis->auth($password)) {
			return null;
		}

		$database = (int) (Environment::get('REDIS_DATABASE', '0') ?? '0');
		if ($database > 0 && !@$redis->select($database)) {
			return null;
		}

		return $redis;
	}
}
