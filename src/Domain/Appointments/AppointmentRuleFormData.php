<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Appointments;

final readonly class AppointmentRuleFormData
{
	/**
	 * @param array<string, string> $effects
	 * @param list<string> $roleIds
	 * @param list<int> $userIds
	 * @param list<int> $locationIds
	 * @param list<int> $serviceIds
	 * @param list<int> $appointmentIds
	 */
	public function __construct(
		public string $name,
		public bool $active,
		public array $effects,
		public array $roleIds,
		public array $userIds,
		public array $locationIds,
		public array $serviceIds,
		public array $appointmentIds = []
	) {
	}

	public static function fromArray(array $input): self
	{
		return new self(
			name: trim((string) ($input['name'] ?? '')),
			active: (string) ($input['status'] ?? '') === '1',
			effects: self::effects($input['effects'] ?? []),
			roleIds: self::strings($input['roles'] ?? []),
			userIds: self::integerIds($input['users'] ?? []),
			locationIds: self::integerIds($input['locations'] ?? []),
			serviceIds: self::integerIds($input['services'] ?? []),
			appointmentIds: self::integerIds($input['appointments'] ?? [])
		);
	}

	public static function fromDocument(array $document): self
	{
		$subjects = is_array($document['subjects'] ?? null) ? $document['subjects'] : [];
		$resources = is_array($document['resources'] ?? null) ? $document['resources'] : [];

		return new self(
			name: trim((string) ($document['name'] ?? '')),
			active: (bool) ($document['active'] ?? false),
			effects: self::effects($document['effects'] ?? []),
			roleIds: self::strings($subjects['roles'] ?? []),
			userIds: self::integerIds($subjects['users'] ?? []),
			locationIds: self::integerIds($resources['locations'] ?? []),
			serviceIds: self::integerIds($resources['services'] ?? []),
			appointmentIds: self::integerIds($resources['appointments'] ?? [])
		);
	}

	public function validate(): array
	{
		$errors = [];

		if ($this->name === '') {
			$errors['name'] = 'Rule name is required.';
		}

		if ($this->effects === []) {
			$errors['effects'] = 'Select at least one rule action.';
		}

		return $errors;
	}

	public function toDocument(string $ruleId): array
	{
		return [
			'id' => $ruleId,
			'name' => $this->name,
			'active' => $this->active,
			'effects' => $this->effects,
			'subjects' => [
				'roles' => $this->roleIds,
				'users' => $this->userIds,
			],
			'resources' => [
				'locations' => $this->locationIds,
				'services' => $this->serviceIds,
				'appointments' => $this->appointmentIds,
			],
		];
	}

	private static function effects(mixed $items): array
	{
		if (!is_array($items)) {
			return [];
		}

		$effects = [];
		foreach ($items as $capability => $effect) {
			if (
				is_string($capability)
				&& AppointmentCapability::tryFrom($capability) !== null
				&& is_string($effect)
				&& AppointmentRuleEffect::tryFrom($effect) !== null
			) {
				$effects[$capability] = $effect;
			}
		}

		return $effects;
	}

	private static function strings(mixed $items): array
	{
		if (!is_array($items)) {
			return [];
		}

		$strings = [];
		foreach ($items as $item) {
			if (is_string($item) && trim($item) !== '') {
				$strings[trim($item)] = trim($item);
			}
		}

		return array_values($strings);
	}

	private static function integerIds(mixed $items): array
	{
		if (!is_array($items)) {
			return [];
		}

		$ids = [];
		foreach ($items as $item) {
			$id = filter_var($item, FILTER_VALIDATE_INT);
			if ($id !== false && $id > 0) {
				$ids[(int) $id] = (int) $id;
			}
		}

		return array_values($ids);
	}
}
