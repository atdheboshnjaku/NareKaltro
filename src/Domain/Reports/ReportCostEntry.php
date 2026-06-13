<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Reports;

final readonly class ReportCostEntry
{
	public function __construct(
		public int $appointmentId,
		public int $locationId,
		public ?int $employeeId,
		public int $serviceId,
		public int $year,
		public int $month,
		public string $value
	) {
	}
}
