<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Locations;

use RuntimeException;

final class LocationValidationFailed extends RuntimeException
{
	public function __construct(private array $errors)
	{
		parent::__construct('Location validation failed.');
	}

	public function errors(): array
	{
		return $this->errors;
	}
}
