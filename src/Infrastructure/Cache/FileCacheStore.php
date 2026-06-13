<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Cache;

use Fin\Narekaltro\Domain\Shared\CacheStore;

final class FileCacheStore implements CacheStore
{
	public function __construct(private string $directory)
	{
	}

	#[\Override]
	public function remember(string $key, int $ttlSeconds, callable $resolver): mixed
	{
		$handle = $this->handle($key);
		if ($handle === null) {
			return $resolver();
		}

		try {
			if (!flock($handle, LOCK_EX)) {
				return $resolver();
			}

			$now = time();
			$stored = $this->storedValue($handle);
			if (
				is_array($stored)
				&& ($stored['key'] ?? null) === $key
				&& (int) ($stored['expires_at'] ?? 0) >= $now
			) {
				return $stored['value'] ?? null;
			}

			$value = $resolver();
			$this->writeValue($handle, [
				'key' => $key,
				'expires_at' => $now + max(1, $ttlSeconds),
				'value' => $value,
			]);

			return $value;
		} finally {
			flock($handle, LOCK_UN);
			fclose($handle);
		}
	}

	#[\Override]
	public function forget(string $key): void
	{
		$path = $this->path($key);
		if (is_file($path)) {
			@unlink($path);
		}
	}

	#[\Override]
	public function forgetByPrefix(string $prefix): void
	{
		foreach (glob($this->directory . '/*.cache') ?: [] as $path) {
			$handle = @fopen($path, 'r');
			if ($handle === false) {
				continue;
			}

			$stored = $this->storedValue($handle);
			fclose($handle);

			if (is_array($stored) && str_starts_with((string) ($stored['key'] ?? ''), $prefix)) {
				@unlink($path);
			}
		}
	}

	private function handle(string $key): mixed
	{
		if (!is_dir($this->directory) && !mkdir($this->directory, 0755, true) && !is_dir($this->directory)) {
			return null;
		}

		return @fopen($this->path($key), 'c+');
	}

	private function path(string $key): string
	{
		return $this->directory . '/' . hash('sha256', $key) . '.cache';
	}

	/**
	 * @return array{key?: string, expires_at?: int, value?: mixed}|null
	 */
	private function storedValue(mixed $handle): ?array
	{
		rewind($handle);
		$contents = stream_get_contents($handle);
		if ($contents === false || trim($contents) === '') {
			return null;
		}

		$value = @unserialize($contents, ['allowed_classes' => true]);

		return is_array($value) ? $value : null;
	}

	/**
	 * @param array{key: string, expires_at: int, value: mixed} $value
	 */
	private function writeValue(mixed $handle, array $value): void
	{
		rewind($handle);
		ftruncate($handle, 0);
		fwrite($handle, serialize($value));
		fflush($handle);
	}
}
