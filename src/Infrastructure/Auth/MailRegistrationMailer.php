<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Auth;

use Fin\Narekaltro\Domain\Auth\RegistrationMailer;

final class MailRegistrationMailer implements RegistrationMailer
{
	#[\Override]
	public function sendVerification(string $email, string $hash): bool
	{
		$link = $this->verificationLink($hash);
		$subject = 'Please verify your account';
		$message = '
			<html>
			<head>
				<title>Please verify email</title>
			</head>
			<body>
				<h1>Verify account by clicking on the link below</h1><br>
				<p><a href="' . $link . '">Verify Email</a></p>
				<p>If the link above does not work, please visit this url to verify: <br>
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

	private function verificationLink(string $hash): string
	{
		$host = (string) ($_SERVER['HTTP_HOST'] ?? '');
		if ($host === '') {
			return 'https://fin.narekaltro.com/verify?hash=' . rawurlencode($hash);
		}

		$secure = strtolower((string) ($_SERVER['HTTPS'] ?? '')) === 'on'
			|| (string) ($_SERVER['SERVER_PORT'] ?? '') === '443'
			|| strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';

		return ($secure ? 'https' : 'http') . '://' . $host . '/verify?hash=' . rawurlencode($hash);
	}

}
