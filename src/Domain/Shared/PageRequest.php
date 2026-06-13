<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Shared;

final readonly class PageRequest
{
	public int $page;

	public int $perPage;

	public function __construct(int $page = 1, int $perPage = 25, int $maxPerPage = 100)
	{
		$this->page = max(1, $page);
		$this->perPage = max(1, min($maxPerPage, $perPage));
	}

	public static function fromArray(array $input, int $defaultPerPage = 25, int $maxPerPage = 100): self
	{
		return new self(
			self::positiveInt($input['page'] ?? null, 1),
			self::positiveInt($input['per_page'] ?? null, $defaultPerPage),
			$maxPerPage
		);
	}

	public function offset(): int
	{
		return ($this->page - 1) * $this->perPage;
	}

	public function withinTotal(int $total): self
	{
		$totalPages = max(1, (int) ceil($total / $this->perPage));

		return $this->page <= $totalPages
			? $this
			: new self($totalPages, $this->perPage, $this->perPage);
	}

	private static function positiveInt(mixed $value, int $fallback): int
	{
		if (is_int($value)) {
			return $value > 0 ? $value : $fallback;
		}

		if (!is_string($value) || !ctype_digit($value)) {
			return $fallback;
		}

		$number = (int) $value;

		return $number > 0 ? $number : $fallback;
	}
}
