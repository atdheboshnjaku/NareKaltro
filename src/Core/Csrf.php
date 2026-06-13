<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Core;

final class Csrf
{
	private const SESSION_KEY = '_csrf_token';
	private const FIELD = '_csrf_token';
	private const HEADER = 'HTTP_X_CSRF_TOKEN';

	private function __construct()
	{
	}

	public static function token(): string
	{
		Session::start();

		$token = $_SESSION[self::SESSION_KEY] ?? null;
		if (!is_string($token) || $token === '') {
			$token = bin2hex(random_bytes(32));
			$_SESSION[self::SESSION_KEY] = $token;
		}

		return $token;
	}

	public static function fieldName(): string
	{
		return self::FIELD;
	}

	public static function validate(Request $request): bool
	{
		Session::start();

		$expected = $_SESSION[self::SESSION_KEY] ?? null;
		$provided = $request->input(self::FIELD, $request->server(self::HEADER, null));

		return is_string($expected)
			&& is_string($provided)
			&& $expected !== ''
			&& hash_equals($expected, $provided);
	}
}
