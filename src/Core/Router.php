<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Core;

use Closure;
use Fin\Narekaltro\Domain\Auth\AuthenticationRequired;
use Fin\Narekaltro\Domain\Auth\AuthorizationDenied;
use Fin\Narekaltro\Http\HttpException;
use Throwable;

final class Router
{
	private array $routes = [];

	public function __construct(private Container $container)
	{
	}

	public function get(string $path, array|Closure $handler): void
	{
		$this->add('GET', $path, $handler);
	}

	public function post(string $path, array|Closure $handler): void
	{
		$this->add('POST', $path, $handler);
	}

	public function add(string $method, string $path, array|Closure $handler): void
	{
		$path = '/' . trim($path, '/');
		$this->routes[strtoupper($method)][$path === '/' ? '/' : rtrim($path, '/')] = $handler;
	}

	public function dispatch(Request $request): Response
	{
		$method = $request->method();
		$handler = $this->routes[$method][$request->path()]
			?? ($method === 'HEAD' ? ($this->routes['GET'][$request->path()] ?? null) : null);

		if ($handler === null) {
			return Response::html('Not found', 404);
		}

		if ($request->isUnsafeMethod() && !Csrf::validate($request)) {
			return $request->expectsJson()
				? Response::json(['errors' => ['csrf' => 'Security token expired. Please refresh and try again.']], 403)
				: Response::html('Security token expired. Please refresh and try again.', 403);
		}

		try {
			return $this->toResponse($this->invoke($handler, $request));
		} catch (AuthenticationRequired) {
			return Response::redirect('/login');
		} catch (AuthorizationDenied $exception) {
			return Response::html($exception->getMessage() ?: 'Forbidden', 403);
		} catch (HttpException $exception) {
			return Response::html($exception->getMessage(), $exception->statusCode());
		} catch (Throwable $exception) {
			error_log(sprintf(
				'[MVC] Unhandled %s %s: %s in %s:%d%s%s',
				$request->method(),
				$request->path(),
				$exception->getMessage(),
				$exception->getFile(),
				$exception->getLine(),
				PHP_EOL,
				$exception->getTraceAsString()
			));

			return Response::html('Server error', 500);
		}
	}

	private function invoke(array|Closure $handler, Request $request): mixed
	{
		if ($handler instanceof Closure) {
			return $handler($request);
		}

		[$controllerClass, $method] = $handler;
		$controller = $this->container->get($controllerClass);

		return $controller->{$method}($request);
	}

	private function toResponse(mixed $value): Response
	{
		if ($value instanceof Response) {
			return $value;
		}

		if (is_array($value)) {
			return Response::json($value);
		}

		return Response::html((string) $value);
	}
}
