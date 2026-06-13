<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Auth;

final class PasswordResetTokenService
{
	private const TTL_SECONDS = 3600;

	public function __construct(private string $secret)
	{
	}

	public function issue(LoginUser $loginUser): string
	{
		$payload = $this->encode([
			'user_id' => $loginUser->user->id,
			'exp' => time() + self::TTL_SECONDS,
			'nonce' => bin2hex(random_bytes(16)),
		]);

		return $payload . '.' . $this->signature($payload, $loginUser->passwordHash);
	}

	public function userId(string $token): ?int
	{
		$payload = $this->payload($token);
		if ($payload === null || !isset($payload['user_id']) || !isset($payload['exp'])) {
			return null;
		}

		if ((int) $payload['exp'] < time()) {
			return null;
		}

		$userId = (int) $payload['user_id'];

		return $userId > 0 ? $userId : null;
	}

	public function isValidFor(string $token, LoginUser $loginUser): bool
	{
		$parts = explode('.', $token, 2);
		if (count($parts) !== 2 || $this->userId($token) !== $loginUser->user->id) {
			return false;
		}

		return hash_equals($this->signature($parts[0], $loginUser->passwordHash), $parts[1]);
	}

	private function signature(string $payload, string $passwordHash): string
	{
		return $this->base64UrlEncode(hash_hmac(
			'sha256',
			$payload,
			hash('sha256', $this->secret . '|' . $passwordHash, true),
			true
		));
	}

	/**
	 * @param array<string, mixed> $payload
	 */
	private function encode(array $payload): string
	{
		return $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));
	}

	/**
	 * @return array<string, mixed>|null
	 */
	private function payload(string $token): ?array
	{
		$parts = explode('.', $token, 2);
		if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
			return null;
		}

		$encoded = strtr($parts[0], '-_', '+/');
		$encoded .= str_repeat('=', (4 - strlen($encoded) % 4) % 4);
		$json = base64_decode($encoded, true);
		if ($json === false) {
			return null;
		}

		try {
			$payload = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
		} catch (\JsonException) {
			return null;
		}

		return is_array($payload) ? $payload : null;
	}

	private function base64UrlEncode(string $value): string
	{
		return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
	}
}
