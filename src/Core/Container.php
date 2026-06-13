<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Core;

use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

final class Container
{
	private array $bindings = [];

	private array $instances = [];

	public function set(string $id, mixed $concrete): void
	{
		$this->bindings[$id] = $concrete;
	}

	public function get(string $id): mixed
	{
		if (array_key_exists($id, $this->instances)) {
			return $this->instances[$id];
		}

		if (array_key_exists($id, $this->bindings)) {
			$concrete = $this->bindings[$id];
			$value = is_callable($concrete) ? $concrete($this) : $concrete;

			return $this->instances[$id] = $value;
		}

		if (!class_exists($id)) {
			throw new RuntimeException("Nothing is bound in the container for [{$id}].");
		}

		return $this->instances[$id] = $this->build($id);
	}

	private function build(string $className): object
	{
		$reflection = new ReflectionClass($className);

		if (!$reflection->isInstantiable()) {
			throw new RuntimeException("Class [{$className}] cannot be instantiated.");
		}

		$constructor = $reflection->getConstructor();
		if ($constructor === null) {
			return new $className();
		}

		$dependencies = [];
		foreach ($constructor->getParameters() as $parameter) {
			$type = $parameter->getType();

			if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
				$dependencies[] = $this->get($type->getName());
				continue;
			}

			if ($parameter->isDefaultValueAvailable()) {
				$dependencies[] = $parameter->getDefaultValue();
				continue;
			}

			throw new RuntimeException(
				"Cannot resolve parameter [{$parameter->getName()}] for [{$className}]."
			);
		}

		return $reflection->newInstanceArgs($dependencies);
	}
}
