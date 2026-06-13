<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Clients;

final readonly class ClientFormData
{
	public function __construct(
		public int $locationId,
		public string $name,
		public string $email,
		public string $phone,
		public int $countryId,
		public int $stateId,
		public int $cityId,
	) {
	}

	public static function fromArray(array $input): self
	{
		return new self(
			locationId: max(0, (int) ($input['location_id'] ?? 0)),
			name: trim((string) ($input['name'] ?? '')),
			email: trim((string) ($input['email'] ?? '')),
			phone: trim((string) ($input['number'] ?? '')),
			countryId: max(0, (int) ($input['country'] ?? 0)),
			stateId: max(0, (int) ($input['state'] ?? 0)),
			cityId: max(0, (int) ($input['city'] ?? 0)),
		);
	}

	public static function fromClient(ClientProfile $client): self
	{
		return new self(
			locationId: $client->locationId,
			name: $client->name,
			email: $client->email,
			phone: $client->phone,
			countryId: $client->countryId,
			stateId: $client->stateId,
			cityId: $client->cityId,
		);
	}

	public function validate(): array
	{
		$errors = [];

		if ($this->locationId < 1) {
			$errors['location_id'] = 'Please select a client location.';
		}

		if ($this->name === '') {
			$errors['name'] = 'Please enter the client name.';
		}

		if ($this->email !== '' && filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
			$errors['email'] = 'Please enter a valid email address.';
		}

		if ($this->countryId < 1) {
			$errors['country'] = 'Please select the client country.';
		}

		return $errors;
	}
}
