<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Core;

final class Request
{
	public function __construct(
		private string $method,
		private string $path,
		private array $query = [],
		private array $body = [],
		private array $cookies = [],
		private array $server = []
	) {
	}

	public static function capture(): self
	{
		$uri = $_SERVER['REQUEST_URI'] ?? '/';
		$path = parse_url($uri, PHP_URL_PATH) ?: '/';
		$path = '/' . trim($path, '/');

		return new self(
			strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
			$path === '/' ? '/' : rtrim($path, '/'),
			$_GET,
			$_POST,
			$_COOKIE,
			$_SERVER
		);
	}

	public function method(): string
	{
		return $this->method;
	}

	public function isUnsafeMethod(): bool
	{
		return !in_array($this->method, ['GET', 'HEAD', 'OPTIONS'], true);
	}

	public function expectsJson(): bool
	{
		$accept = strtolower((string) $this->server('HTTP_ACCEPT', ''));
		$requestedWith = strtolower((string) $this->server('HTTP_X_REQUESTED_WITH', ''));

		return str_contains($accept, 'application/json')
			|| $requestedWith === 'xmlhttprequest';
	}

	public function path(): string
	{
		return $this->path;
	}

	public function query(string $key, mixed $default = null): mixed
	{
		return $this->query[$key] ?? $default;
	}

	public function input(string $key, mixed $default = null): mixed
	{
		return $this->body[$key] ?? $this->query($key, $default);
	}

	public function all(): array
	{
		return array_merge($this->query, $this->body);
	}

	public function cookie(string $key, mixed $default = null): mixed
	{
		return $this->cookies[$key] ?? $default;
	}

	public function server(string $key, mixed $default = null): mixed
	{
		return $this->server[$key] ?? $default;
	}
}
