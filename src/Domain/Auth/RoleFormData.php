<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Auth;

final readonly class RoleFormData
{
	/** @param list<string> $permissions */
	public function __construct(
		public string $name,
		public ?string $description,
		public bool $active,
		public array $permissions,
	) {
	}

	public static function fromArray(array $input): self
	{
		$permissions = $input['permissions'] ?? [];

		return new self(
			name: trim((string) ($input['name'] ?? '')),
			description: self::nullableString($input['description'] ?? null),
			active: (string) ($input['status'] ?? '') === '1',
			permissions: self::normalizePermissions(is_array($permissions) ? $permissions : []),
		);
	}

	public function validate(): array
	{
		$errors = [];

		if ($this->name === '') {
			$errors['name'] = 'Role name is required.';
		}

		return $errors;
	}

	private static function nullableString(mixed $value): ?string
	{
		$value = trim((string) $value);

		return $value === '' ? null : $value;
	}

	/** @return list<string> */
	private static function normalizePermissions(array $permissions): array
	{
		$normalized = [];

		foreach ($permissions as $permission) {
			if (!is_string($permission)) {
				continue;
			}

			$permission = trim($permission);
			if ($permission === '') {
				continue;
			}

			$normalized[$permission] = $permission;
		}

		return array_values($normalized);
	}
}
