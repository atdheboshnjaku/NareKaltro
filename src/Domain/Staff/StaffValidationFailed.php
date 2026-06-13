<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Staff;

use RuntimeException;

final class StaffValidationFailed extends RuntimeException
{
	public function __construct(private array $errors)
	{
		parent::__construct('Staff validation failed.');
	}

	public function errors(): array
	{
		return $this->errors;
	}
}
