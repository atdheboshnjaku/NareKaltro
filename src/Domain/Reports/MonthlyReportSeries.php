<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Reports;

final readonly class MonthlyReportSeries
{
	/** @param list<float> $values */
	private function __construct(
		public int $year,
		public array $values
	) {
	}

	/** @param array<int, int|float|string> $totals */
	public static function fromTotals(int $year, array $totals): self
	{
		$values = array_fill(0, 12, 0.0);

		foreach ($totals as $month => $total) {
			if ($month >= 1 && $month <= 12) {
				$values[$month - 1] = round((float) $total, 2);
			}
		}

		return new self($year, $values);
	}

	public function total(): float
	{
		return round(array_sum($this->values), 2);
	}

	public function toArray(): array
	{
		return [
			'year' => $this->year,
			'values' => $this->values,
			'total' => $this->total(),
		];
	}
}
