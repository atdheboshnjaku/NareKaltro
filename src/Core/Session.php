<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Core;

final class Session
{
	private function __construct()
	{
	}

	public static function start(): void
	{
		if (session_status() !== PHP_SESSION_NONE) {
			return;
		}

		$savePath = session_save_path();
		if (PHP_SAPI === 'cli' && $savePath !== '' && !is_writable($savePath)) {
			session_save_path(sys_get_temp_dir());
		}

		session_set_cookie_params([
			'lifetime' => 0,
			'path' => '/',
			'secure' => self::isSecureRequest(),
			'httponly' => true,
			'samesite' => 'Lax',
		]);

		session_start();
	}

	public static function isSecureRequest(): bool
	{
		return strtolower((string) ($_SERVER['HTTPS'] ?? '')) === 'on'
			|| (string) ($_SERVER['SERVER_PORT'] ?? '') === '443'
			|| strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
	}
}
