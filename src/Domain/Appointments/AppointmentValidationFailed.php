<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Appointments;

use RuntimeException;

final class AppointmentValidationFailed extends RuntimeException
{
	public function __construct(private readonly array $validationErrors)
	{
		parent::__construct('The appointment could not be saved.');
	}

	public function errors(): array
	{
		return $this->validationErrors;
	}
}
