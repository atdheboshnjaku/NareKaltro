<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Core;

final class Response
{
	public function __construct(
		private string $content = '',
		private int $status = 200,
		private array $headers = []
	) {
	}

	public static function html(string $content, int $status = 200): self
	{
		return new self($content, $status, ['Content-Type' => 'text/html; charset=utf-8']);
	}

	public static function json(array $payload, int $status = 200): self
	{
		return new self(
			json_encode($payload, JSON_THROW_ON_ERROR),
			$status,
			['Content-Type' => 'application/json; charset=utf-8']
		);
	}

	public static function redirect(string $to, int $status = 302): self
	{
		return new self('', $status, ['Location' => $to]);
	}

	public function send(): void
	{
		http_response_code($this->status);

		foreach ($this->headersWithSecurityDefaults() as $name => $value) {
			header("{$name}: {$value}");
		}

		echo $this->content;
	}

	private function headersWithSecurityDefaults(): array
	{
		return $this->headers + [
			'X-Frame-Options' => 'SAMEORIGIN',
			'X-Content-Type-Options' => 'nosniff',
			'Referrer-Policy' => 'strict-origin-when-cross-origin',
			'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
		];
	}
}
