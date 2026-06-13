<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Reports;

final readonly class ReportFilters
{
	public function __construct(
		public ?int $locationId = null,
		public ?int $employeeId = null
	) {
	}

	public static function none(): self
	{
		return new self();
	}

	public static function fromArray(array $input): self
	{
		return new self(
			locationId: self::positiveInt($input['location_id'] ?? null),
			employeeId: self::positiveInt($input['employee_id'] ?? null)
		);
	}

	public function toArray(): array
	{
		return [
			'locationId' => $this->locationId,
			'employeeId' => $this->employeeId,
		];
	}

	private static function positiveInt(mixed $value): ?int
	{
		$value = (int) $value;

		return $value > 0 ? $value : null;
	}
}
