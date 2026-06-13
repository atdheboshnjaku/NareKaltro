<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Clients;

use RuntimeException;

final class ClientValidationFailed extends RuntimeException
{
	public function __construct(private array $errors)
	{
		parent::__construct('Client validation failed.');
	}

	public function errors(): array
	{
		return $this->errors;
	}
}
