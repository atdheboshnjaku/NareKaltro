<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Auth;

interface RegistrationMailer
{
	public function sendVerification(string $email, string $hash): bool;
}
