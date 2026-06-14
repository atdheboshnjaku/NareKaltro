<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Services;

final readonly class ServiceOffering
{
	public function __construct(
		public int $id,
		public string $accountId,
		public string $name,
		public string $background,
		public string $color,
		public bool $quoteOnly,
		public bool $active,
	) {
	}

	public static function fromRow(array $row): self
	{
		return new self(
			id: (int) $row['id'],
			accountId: (string) $row['account_id'],
			name: (string) $row['name'],
			background: (string) ($row['background'] ?: '#f1faff'),
			color: (string) ($row['color'] ?: '#009ef7'),
			quoteOnly: (int) ($row['quote_only'] ?? 0) === 1,
			active: (int) $row['status'] === 1,
		);
	}
}
