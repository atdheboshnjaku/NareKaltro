<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Cache;

use Fin\Narekaltro\Domain\Shared\CacheStore;

final class ArrayCacheStore implements CacheStore
{
	/** @var array<string, array{expires_at: int, value: mixed}> */
	private array $items = [];

	#[\Override]
	public function remember(string $key, int $ttlSeconds, callable $resolver): mixed
	{
		$now = time();
		$item = $this->items[$key] ?? null;

		if ($item !== null && $item['expires_at'] >= $now) {
			return $item['value'];
		}

		$value = $resolver();
		$this->items[$key] = [
			'expires_at' => $now + max(1, $ttlSeconds),
			'value' => $value,
		];

		return $value;
	}

	#[\Override]
	public function forget(string $key): void
	{
		unset($this->items[$key]);
	}

	#[\Override]
	public function forgetByPrefix(string $prefix): void
	{
		foreach (array_keys($this->items) as $key) {
			if (str_starts_with($key, $prefix)) {
				unset($this->items[$key]);
			}
		}
	}
}
