<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Auth;

final readonly class UserAccessFormData
{
	public function __construct(
		public array $roles,
		public array $allow,
		public array $deny,
	) {
	}

	public static function fromArray(array $input): self
	{
		return new self(
			roles: self::strings($input['roles'] ?? []),
			allow: self::strings($input['allow'] ?? []),
			deny: self::strings($input['deny'] ?? []),
		);
	}

	public function validate(): array
	{
		$errors = [];

		if ($this->roles === []) {
			$errors['roles'] = 'Select at least one role.';
		}

		if (array_intersect($this->allow, $this->deny) !== []) {
			$errors['permissions'] = 'A permission cannot be both explicitly allowed and denied.';
		}

		return $errors;
	}

	private static function strings(mixed $values): array
	{
		if (!is_array($values)) {
			return [];
		}

		$strings = array_map(
			static fn (mixed $value): string => trim((string) $value),
			$values
		);

		return array_values(array_unique(array_filter(
			$strings,
			static fn (string $value): bool => $value !== ''
		)));
	}
}
