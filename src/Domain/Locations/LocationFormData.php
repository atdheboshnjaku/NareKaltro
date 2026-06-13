<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Locations;

final readonly class LocationFormData
{
	public function __construct(public string $name)
	{
	}

	public static function fromArray(array $input): self
	{
		return new self(name: trim((string) ($input['name'] ?? '')));
	}

	public function validate(): array
	{
		return $this->name === '' ? ['name' => 'Location name is required.'] : [];
	}
}
