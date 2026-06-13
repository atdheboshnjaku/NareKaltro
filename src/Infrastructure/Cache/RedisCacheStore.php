<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Cache;

use Fin\Narekaltro\Domain\Shared\CacheStore;

final class RedisCacheStore implements CacheStore
{
	public function __construct(
		private \Redis $redis,
		private string $prefix = 'narekaltro:'
	) {
	}

	#[\Override]
	public function remember(string $key, int $ttlSeconds, callable $resolver): mixed
	{
		$redisKey = $this->redisKey($key);
		$cached = $this->redis->get($redisKey);

		if (is_string($cached)) {
			$value = @unserialize($cached, ['allowed_classes' => true]);
			if ($value !== false || $cached === serialize(false)) {
				return $value;
			}
		}

		$value = $resolver();
		$this->redis->setex($redisKey, max(1, $ttlSeconds), serialize($value));

		return $value;
	}

	#[\Override]
	public function forget(string $key): void
	{
		$this->redis->del($this->redisKey($key));
	}

	#[\Override]
	public function forgetByPrefix(string $prefix): void
	{
		foreach ($this->keys($this->redisKey($prefix) . '*') as $key) {
			$this->redis->del($key);
		}
	}

	private function redisKey(string $key): string
	{
		return $this->prefix . $key;
	}

	/** @return list<string> */
	private function keys(string $pattern): array
	{
		$iterator = null;
		$keys = [];

		do {
			$scan = $this->redis->scan($iterator, $pattern, 100);
			if ($scan === false) {
				break;
			}

			foreach ($scan as $key) {
				if (is_string($key)) {
					$keys[] = $key;
				}
			}
		} while ($iterator !== 0);

		return $keys;
	}
}
