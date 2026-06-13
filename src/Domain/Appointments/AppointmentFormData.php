<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Appointments;

final readonly class AppointmentFormData
{
	/**
	 * @param list<int> $serviceIds
	 * @param array<int, string> $costs
	 */
	public function __construct(
		public int $locationId,
		public int $employeeId,
		public int $clientId,
		public array $serviceIds,
		public string $startDate,
		public ?string $endDate,
		public string $notes,
		public array $costs = []
	) {
	}

	public static function fromArray(array $input, int $clientId = 0): self
	{
		$postedClientId = (int) ($input['client_id'] ?? 0);

		return new self(
			locationId: max(0, (int) ($input['location_id'] ?? 0)),
			employeeId: max(0, (int) ($input['employee_id'] ?? 0)),
			clientId: $clientId > 0 ? $clientId : max(0, $postedClientId),
			serviceIds: self::integerIds($input['service_ids'] ?? $input['service_id'] ?? []),
			startDate: trim((string) ($input['start_date'] ?? '')),
			endDate: self::optionalString($input['end_date'] ?? null),
			notes: trim((string) ($input['appointment_notes'] ?? '')),
			costs: self::costValues($input['service_cost'] ?? [])
		);
	}

	/** @return list<int> */
	private static function integerIds(mixed $input): array
	{
		if (is_string($input)) {
			$input = explode(',', $input);
		}

		if (!is_array($input)) {
			return [];
		}

		$ids = [];
		foreach ($input as $value) {
			$id = (int) $value;
			if ($id > 0) {
				$ids[$id] = $id;
			}
		}

		return array_values($ids);
	}

	/** @return array<int, string> */
	private static function costValues(mixed $input): array
	{
		if (!is_array($input)) {
			return [];
		}

		$costs = [];
		foreach ($input as $serviceId => $cost) {
			$id = (int) $serviceId;
			if ($id > 0 && (is_string($cost) || is_numeric($cost))) {
				$costs[$id] = trim((string) $cost);
			}
		}

		return $costs;
	}

	private static function optionalString(mixed $value): ?string
	{
		$value = trim((string) $value);

		return $value === '' ? null : $value;
	}
}
