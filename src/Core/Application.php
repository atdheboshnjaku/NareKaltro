<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Core;

final class Application
{
	public function __construct(private Router $router)
	{
	}

	public function handle(Request $request): Response
	{
		return $this->router->dispatch($request);
	}
}
