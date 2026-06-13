<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Auth;

use RuntimeException;

final class UserAccessValidationFailed extends RuntimeException
{
	public function __construct(private array $errors)
	{
		parent::__construct('User access validation failed.');
	}

	public function errors(): array
	{
		return $this->errors;
	}
}
