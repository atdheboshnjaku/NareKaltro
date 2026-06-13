<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Shared;

/**
 * @template T
 */
final readonly class PageResult
{
	/**
	 * @param list<T> $items
	 */
	public function __construct(
		public array $items,
		public int $total,
		public PageRequest $request
	) {
	}

	/**
	 * @template TItem
	 * @param list<TItem> $items
	 * @return self<TItem>
	 */
	public static function fromItems(array $items, PageRequest $request): self
	{
		$request = $request->withinTotal(count($items));

		return new self(
			array_slice($items, $request->offset(), $request->perPage),
			count($items),
			$request
		);
	}

	public function totalPages(): int
	{
		return max(1, (int) ceil($this->total / $this->request->perPage));
	}

	public function hasPrevious(): bool
	{
		return $this->request->page > 1;
	}

	public function hasNext(): bool
	{
		return $this->request->page < $this->totalPages();
	}

	public function firstItemNumber(): int
	{
		return $this->total === 0 ? 0 : $this->request->offset() + 1;
	}

	public function lastItemNumber(): int
	{
		return min($this->total, $this->request->offset() + count($this->items));
	}
}
