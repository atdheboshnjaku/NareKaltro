<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Staff;

final readonly class StaffFormData
{
	/** @param list<string> $roles */
	public function __construct(
		public int $locationId,
		public string $name,
		public string $email,
		public string $password,
		public array $roles,
	) {
	}

	public static function fromArray(array $input): self
	{
		return new self(
			locationId: max(0, (int) ($input['location_id'] ?? 0)),
			name: trim((string) ($input['name'] ?? '')),
			email: trim((string) ($input['email'] ?? '')),
			password: (string) ($input['password'] ?? ''),
			roles: self::normalizeRoles($input['roles'] ?? []),
		);
	}

	public static function fromMember(StaffMember $member, array $roles): self
	{
		return new self(
			locationId: $member->locationId,
			name: $member->name,
			email: $member->email ?? '',
			password: '',
			roles: self::normalizeRoles($roles),
		);
	}

	public function validate(bool $passwordRequired): array
	{
		$errors = [];

		if ($this->locationId < 1) {
			$errors['location_id'] = 'Please select a user location.';
		}

		if ($this->name === '') {
			$errors['name'] = 'Please enter the users full name.';
		}

		if ($this->email === '' || filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
			$errors['email'] = 'Please enter a valid email address.';
		}

		if ($passwordRequired && $this->password === '') {
			$errors['password'] = 'Please enter a password.';
		}

		if ($this->roles === []) {
			$errors['roles'] = 'Select at least one role.';
		}

		return $errors;
	}

	/** @return list<string> */
	private static function normalizeRoles(mixed $roles): array
	{
		if (!is_array($roles)) {
			return [];
		}

		$normalized = [];
		foreach ($roles as $role) {
			$role = trim((string) $role);
			if ($role !== '') {
				$normalized[$role] = $role;
			}
		}

		return array_values($normalized);
	}
}
