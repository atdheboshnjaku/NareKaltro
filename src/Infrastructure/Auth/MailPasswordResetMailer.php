<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Auth;

use Fin\Narekaltro\Domain\Auth\PasswordResetMailer;

final class MailPasswordResetMailer implements PasswordResetMailer
{
	#[\Override]
	public function sendResetLink(string $email, string $token): bool
	{
		$link = $this->resetLink($token);
		$subject = 'Reset your password';
		$message = '
			<html>
			<head>
				<title>Reset your password</title>
			</head>
			<body>
				<h1>Reset your password</h1><br>
				<p><a href="' . $link . '">Reset Password</a></p>
				<p>This password reset link expires in 1 hour.</p>
				<p>If the link above does not work, please visit this url: <br>
				' . $link . '
				</p>
			</body>
			</html>';
		$headers = 'MIME-Version: 1.0' . "\r\n" .
			'Content-Type: text/html; charset=UTF-8' . "\r\n" .
			'From: noreply@narekaltro.com' . "\r\n" .
			'Reply-To: noreply@narekaltro.com' . "\r\n" .
			'X-Mailer: PHP/' . phpversion();


		return mail($email, $subject, $message, $headers);
	}

	private function resetLink(string $token): string
	{
		$host = (string) ($_SERVER['HTTP_HOST'] ?? '');
		if ($host === '') {
			return 'https://fin.narekaltro.com/reset-password?token=' . rawurlencode($token);
		}

		$secure = strtolower((string) ($_SERVER['HTTPS'] ?? '')) === 'on'
			|| (string) ($_SERVER['SERVER_PORT'] ?? '') === '443'
			|| strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';

		return ($secure ? 'https' : 'http') . '://' . $host . '/reset-password?token=' . rawurlencode($token);
	}

}
