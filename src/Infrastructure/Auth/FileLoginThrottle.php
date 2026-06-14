<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Auth;

use Fin\Narekaltro\Domain\Auth\LoginThrottle;
use Fin\Narekaltro\Domain\Auth\RegistrationThrottleDecision;

final class FileLoginThrottle implements LoginThrottle
{
	public function __construct(
		private string $path,
		private int $emailLimit = 5,
		private int $emailWindowSeconds = 900,
		private int $ipLimit = 50,
		private int $ipWindowSeconds = 3600
	) {
	}

	#[\Override]
	public function check(string $email, string $ipAddress): RegistrationThrottleDecision
	{
		$handle = $this->lock();
		if ($handle === null) {
			return RegistrationThrottleDecision::allow();
		}

		$now = time();
		$records = $this->prune($this->records($handle), $now);
		$this->release($handle);

		return $this->decision($records, $email, $ipAddress, $now);
	}

	#[\Override]
	public function registerFailure(string $email, string $ipAddress): void
	{
		$handle = $this->lock();
		if ($handle === null) {
			return;
		}

		$now = time();
		$records = $this->prune($this->records($handle), $now);
		$records[$this->emailKey($email)][] = $now;
		$records[$this->ipKey($ipAddress)][] = $now;

		$this->persist($handle, $records);
	}

	#[\Override]
	public function clear(string $email, string $ipAddress): void
	{
		$handle = $this->lock();
		if ($handle === null) {
			return;
		}

		$records = $this->prune($this->records($handle), time());
		unset($records[$this->emailKey($email)]);

		$this->persist($handle, $records);
	}

	/**
	 * @param array<string, list<int>> $records
	 */
	private function decision(array $records, string $email, string $ipAddress, int $now): RegistrationThrottleDecision
	{
		$emailAttempts = $records[$this->emailKey($email)] ?? [];
		if (count($emailAttempts) >= $this->emailLimit) {
			$retryAfter = min($emailAttempts) + $this->emailWindowSeconds - $now;
			if ($retryAfter > 0) {
				return RegistrationThrottleDecision::deny($retryAfter);
			}
		}

		$ipAttempts = $records[$this->ipKey($ipAddress)] ?? [];
		if (count($ipAttempts) >= $this->ipLimit) {
			$retryAfter = min($ipAttempts) + $this->ipWindowSeconds - $now;
			if ($retryAfter > 0) {
				return RegistrationThrottleDecision::deny($retryAfter);
			}
		}

		return RegistrationThrottleDecision::allow();
	}

	private function lock(): mixed
	{
		$directory = dirname($this->path);
		if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
			return null;
		}

		$handle = fopen($this->path, 'c+');
		if ($handle === false) {
			return null;
		}

		if (!flock($handle, LOCK_EX)) {
			fclose($handle);

			return null;
		}

		return $handle;
	}

	/**
	 * @return array<string, list<int>>
	 */
	private function records(mixed $handle): array
	{
		rewind($handle);
		$contents = stream_get_contents($handle);
		if ($contents === false || trim($contents) === '') {
			return [];
		}

		$decoded = json_decode($contents, true);
		if (!is_array($decoded)) {
			return [];
		}

		$records = [];
		foreach ($decoded as $key => $attempts) {
			if (!is_string($key) || !is_array($attempts)) {
				continue;
			}

			$records[$key] = array_values(array_filter(
				array_map(static fn (mixed $attempt): int => (int) $attempt, $attempts),
				static fn (int $attempt): bool => $attempt > 0
			));
		}

		return $records;
	}

	/**
	 * @param array<string, list<int>> $records
	 * @return array<string, list<int>>
	 */
	private function prune(array $records, int $now): array
	{
		$pruned = [];
		foreach ($records as $key => $attempts) {
			$window = str_starts_with($key, 'email:')
				? $this->emailWindowSeconds
				: $this->ipWindowSeconds;
			$attempts = array_values(array_filter(
				$attempts,
				static fn (int $attempt): bool => $attempt >= $now - $window
			));

			if ($attempts !== []) {
				$pruned[$key] = $attempts;
			}
		}

		return $pruned;
	}

	/**
	 * @param array<string, list<int>> $records
	 */
	private function persist(mixed $handle, array $records): void
	{
		rewind($handle);
		ftruncate($handle, 0);
		fwrite($handle, json_encode($records, JSON_UNESCAPED_SLASHES));
		fflush($handle);
		flock($handle, LOCK_UN);
		fclose($handle);
	}

	private function release(mixed $handle): void
	{
		flock($handle, LOCK_UN);
		fclose($handle);
	}

	private function emailKey(string $email): string
	{
		return 'email:' . hash('sha256', strtolower(trim($email)));
	}

	private function ipKey(string $ipAddress): string
	{
		$ipAddress = trim($ipAddress);

		return 'ip:' . hash('sha256', $ipAddress === '' ? 'unknown' : $ipAddress);
	}
}
