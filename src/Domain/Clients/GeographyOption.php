<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Clients;

final readonly class GeographyOption
{
	public function __construct(
		public int $id,
		public string $name,
	) {
	}

	public static function fromRow(array $row): self
	{
		return new self((int) $row['id'], (string) $row['name']);
	}

	public function toArray(): array
	{
		return ['id' => $this->id, 'name' => $this->name];
	}
}
