<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Auth;

use Fin\Narekaltro\Domain\Auth\RegistrationThrottle;
use Fin\Narekaltro\Domain\Auth\RegistrationThrottleDecision;
use Fin\Narekaltro\Domain\Auth\PasswordResetThrottle;

final class FileRegistrationThrottle implements RegistrationThrottle, PasswordResetThrottle
{
	public function __construct(
		private string $path,
		private int $emailCooldownSeconds = 600,
		private int $ipLimit = 10,
		private int $ipWindowSeconds = 3600
	) {
	}

	#[\Override]
	public function attempt(string $email, string $ipAddress): RegistrationThrottleDecision
	{
		$directory = dirname($this->path);
		if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
			return RegistrationThrottleDecision::allow();
		}

		$handle = fopen($this->path, 'c+');
		if ($handle === false) {
			return RegistrationThrottleDecision::allow();
		}

		if (!flock($handle, LOCK_EX)) {
			fclose($handle);

			return RegistrationThrottleDecision::allow();
		}

		$now = time();
		$records = $this->records($handle);
		$records = $this->prune($records, $now);
		$emailKey = 'email:' . hash('sha256', strtolower(trim($email)));
		$ipKey = 'ip:' . hash('sha256', trim($ipAddress) === '' ? 'unknown' : trim($ipAddress));
		$emailAttempts = $records[$emailKey] ?? [];
		$ipAttempts = $records[$ipKey] ?? [];
		$decision = $this->decision($emailAttempts, $ipAttempts, $now);

		if ($decision->allowed) {
			$records[$emailKey][] = $now;
			$records[$ipKey][] = $now;
		}

		$this->writeRecords($handle, $records);
		flock($handle, LOCK_UN);
		fclose($handle);

		return $decision;
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
				? $this->emailCooldownSeconds
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
	 * @param list<int> $emailAttempts
	 * @param list<int> $ipAttempts
	 */
	private function decision(array $emailAttempts, array $ipAttempts, int $now): RegistrationThrottleDecision
	{
		if ($emailAttempts !== []) {
			$latestEmailAttempt = max($emailAttempts);
			$retryAfter = $latestEmailAttempt + $this->emailCooldownSeconds - $now;
			if ($retryAfter > 0) {
				return RegistrationThrottleDecision::deny($retryAfter);
			}
		}

		if (count($ipAttempts) >= $this->ipLimit) {
			$firstIpAttempt = min($ipAttempts);

			return RegistrationThrottleDecision::deny($firstIpAttempt + $this->ipWindowSeconds - $now);
		}

		return RegistrationThrottleDecision::allow();
	}

	/**
	 * @param array<string, list<int>> $records
	 */
	private function writeRecords(mixed $handle, array $records): void
	{
		rewind($handle);
		ftruncate($handle, 0);
		fwrite($handle, json_encode($records, JSON_UNESCAPED_SLASHES));
		fflush($handle);
	}
}
