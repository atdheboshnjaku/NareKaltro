<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Auth;

use RuntimeException;

final class RoleValidationFailed extends RuntimeException
{
	public function __construct(private array $errors)
	{
		parent::__construct('Role validation failed.');
	}

	public function errors(): array
	{
		return $this->errors;
	}
}
