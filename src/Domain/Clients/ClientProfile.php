<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Clients;

final readonly class ClientProfile
{
	public function __construct(
		public int $id,
		public string $accountId,
		public int $locationId,
		public string $name,
		public string $email,
		public string $phone,
		public int $countryId,
		public int $stateId,
		public int $cityId,
		public ?string $locationName,
	) {
	}

	public static function fromRow(array $row): self
	{
		return new self(
			id: (int) $row['id'],
			accountId: (string) $row['account_id'],
			locationId: (int) $row['location_id'],
			name: (string) ($row['name'] ?? ''),
			email: (string) ($row['email'] ?? ''),
			phone: (string) ($row['number'] ?? ''),
			countryId: (int) $row['country'],
			stateId: (int) ($row['state'] ?? 0),
			cityId: (int) ($row['city'] ?? 0),
			locationName: isset($row['location_name']) ? (string) $row['location_name'] : null,
		);
	}

	public function initials(): string
	{
		$words = preg_split('/\s+/', trim($this->name)) ?: [];
		$initials = '';

		foreach (array_slice($words, 0, 2) as $word) {
			$initials .= strtoupper(substr($word, 0, 1));
		}

		return $initials;
	}
}
