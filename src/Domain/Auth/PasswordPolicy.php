<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Auth;

/**
 * Single source of truth for the password rules — drives both server-side
 * enforcement (validate) and the live UI checklist (requirements / patterns).
 */
final class PasswordPolicy
{
	/**
	 * Each requirement carries a JS/PCRE-compatible pattern so the same rule
	 * powers the server check and the front-end strength meter.
	 *
	 * @return list<array{key: string, label: string, pattern: string}>
	 */
	public static function requirements(): array
	{
		return [
			['key' => 'length', 'label' => 'At least 8 characters', 'pattern' => '.{8,}'],
			['key' => 'lower', 'label' => 'One lowercase letter', 'pattern' => '[a-z]'],
			['key' => 'upper', 'label' => 'One uppercase letter', 'pattern' => '[A-Z]'],
			['key' => 'number', 'label' => 'One number', 'pattern' => '[0-9]'],
		];
	}

	/**
	 * @return list<string> labels of the requirements the password fails (empty = valid)
	 */
	public static function validate(string $password): array
	{
		$failed = [];

		foreach (self::requirements() as $requirement) {
			if (preg_match('/' . $requirement['pattern'] . '/', $password) !== 1) {
				$failed[] = $requirement['label'];
			}
		}

		return $failed;
	}

	public static function isValid(string $password): bool
	{
		return self::validate($password) === [];
	}
}
