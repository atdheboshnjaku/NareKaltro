<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Shared;

interface CacheStore
{
	public function remember(string $key, int $ttlSeconds, callable $resolver): mixed;

	public function forget(string $key): void;

	public function forgetByPrefix(string $prefix): void;
}
