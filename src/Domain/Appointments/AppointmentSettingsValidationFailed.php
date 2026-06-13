<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Appointments;

use RuntimeException;

final class AppointmentSettingsValidationFailed extends RuntimeException
{
	public function __construct(private array $errors)
	{
		parent::__construct('Appointment settings validation failed.');
	}

	public function errors(): array
	{
		return $this->errors;
	}
}
