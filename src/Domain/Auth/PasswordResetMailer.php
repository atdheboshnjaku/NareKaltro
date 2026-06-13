<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Auth;

interface PasswordResetMailer
{
	public function sendResetLink(string $email, string $token): bool;
}
