<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Appointments;

final readonly class AppointmentPolicyRule
{
	private function __construct(
		public string $id,
		public string $name,
		public bool $active,
		private array $effects,
		private array $userIds,
		private array $roleIds,
		private array $appointmentIds,
		private array $serviceIds,
		private array $locationIds
	) {
	}

	public static function fromDocument(mixed $document): ?self
	{
		if (!is_array($document)) {
			return null;
		}

		$id = trim((string) ($document['id'] ?? ''));
		if ($id === '') {
			return null;
		}

		$effects = [];
		$effectDocument = is_array($document['effects'] ?? null) ? $document['effects'] : [];
		foreach ($effectDocument as $capability => $effect) {
			if (
				is_string($capability)
				&& AppointmentCapability::tryFrom($capability) !== null
				&& is_string($effect)
				&& AppointmentRuleEffect::tryFrom($effect) !== null
			) {
				$effects[$capability] = $effect;
			}
		}

		if ($effects === []) {
			return null;
		}

		$subjects = is_array($document['subjects'] ?? null) ? $document['subjects'] : [];
		$resources = is_array($document['resources'] ?? null) ? $document['resources'] : [];

		return new self(
			id: $id,
			name: trim((string) ($document['name'] ?? '')),
			active: (bool) ($document['active'] ?? false),
			effects: $effects,
			userIds: self::integerIds($subjects['users'] ?? []),
			roleIds: self::strings($subjects['roles'] ?? []),
			appointmentIds: self::integerIds($resources['appointments'] ?? []),
			serviceIds: self::integerIds($resources['services'] ?? []),
			locationIds: self::integerIds($resources['locations'] ?? [])
		);
	}

	public function effectFor(AppointmentCapability $capability): ?AppointmentRuleEffect
	{
		$effect = $this->effects[$capability->value] ?? null;

		return is_string($effect) ? AppointmentRuleEffect::tryFrom($effect) : null;
	}

	/**
	 * Returns resource specificity followed by subject specificity.
	 *
	 * Appointment, service and location are ranked in that order. For rules
	 * constrained at the same resource level, a user match outranks a role
	 * match, which outranks an account-wide match.
	 *
	 * @param list<string> $roleIds
	 * @return array{int, int, int, int}|null
	 */
	public function specificityFor(
		AppointmentCapability $capability,
		int $userId,
		array $roleIds,
		AppointmentPolicyContext $context
	): ?array {
		if (!$this->active || $this->effectFor($capability) === null) {
			return null;
		}

		if (!$this->contextMatches($context)) {
			return null;
		}

		$subjectSpecificity = $this->subjectSpecificity($userId, $roleIds);
		if ($subjectSpecificity === null) {
			return null;
		}

		return [
			$this->appointmentIds === [] ? 0 : 1,
			$this->serviceIds === [] ? 0 : 1,
			$this->locationIds === [] ? 0 : 1,
			$subjectSpecificity,
		];
	}

	private function contextMatches(AppointmentPolicyContext $context): bool
	{
		return $this->idMatches($this->appointmentIds, $context->appointmentId)
			&& $this->idMatches($this->serviceIds, $context->serviceId)
			&& $this->idMatches($this->locationIds, $context->locationId);
	}

	private function idMatches(array $ruleIds, ?int $contextId): bool
	{
		return $ruleIds === []
			|| ($contextId !== null && in_array($contextId, $ruleIds, true));
	}

	/** @param list<string> $roleIds */
	private function subjectSpecificity(int $userId, array $roleIds): ?int
	{
		if (in_array($userId, $this->userIds, true)) {
			return 2;
		}

		if (array_intersect($roleIds, $this->roleIds) !== []) {
			return 1;
		}

		if ($this->userIds === [] && $this->roleIds === []) {
			return 0;
		}

		return null;
	}

	/** @return list<int> */
	private static function integerIds(mixed $items): array
	{
		if (!is_array($items)) {
			return [];
		}

		$ids = [];
		foreach ($items as $item) {
			$id = filter_var($item, FILTER_VALIDATE_INT);
			if ($id !== false && $id > 0) {
				$ids[] = (int) $id;
			}
		}

		return array_values(array_unique($ids));
	}

	/** @return list<string> */
	private static function strings(mixed $items): array
	{
		if (!is_array($items)) {
			return [];
		}

		$strings = [];
		foreach ($items as $item) {
			if (is_string($item) && trim($item) !== '') {
				$strings[] = trim($item);
			}
		}

		return array_values(array_unique($strings));
	}
}
