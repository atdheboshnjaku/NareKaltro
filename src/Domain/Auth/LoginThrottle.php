<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Auth;

interface LoginThrottle
{
	public function check(string $email, string $ipAddress): RegistrationThrottleDecision;

	public function registerFailure(string $email, string $ipAddress): void;

	public function clear(string $email, string $ipAddress): void;
}
