<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Http;

use RuntimeException;

class HttpException extends RuntimeException
{
	public function __construct(
		private int $statusCode,
		string $message = ''
	) {
		parent::__construct($message);
	}

	public function statusCode(): int
	{
		return $this->statusCode;
	}
}
