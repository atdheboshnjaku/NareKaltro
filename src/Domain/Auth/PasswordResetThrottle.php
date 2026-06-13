<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Auth;

interface PasswordResetThrottle
{
	public function attempt(string $email, string $ipAddress): RegistrationThrottleDecision;
}
