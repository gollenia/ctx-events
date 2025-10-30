<?php

namespace Contexis\Events\Core;

class Container {
	private static ?self $instance = null;
	private array $registry = [];

	public static function getInstance(): self {
		return self::$instance ??= new self();
	}

	public function bind($class, $service): void {
		$this->registry[$class] = $service;
	}

	public function get(string $class): object {
		if (isset($this->registry[$class])) {
			$entry = $this->registry[$class];
			if ($entry instanceof \Closure) {
				$entry = $entry(); // <- hier wird die Closure ausgeführt
				$this->registry[$class] = $entry; // Cache das Ergebnis
			}
			return $entry;
    }

    return $this->build($class); // Autowiring
	}

	private function build(string $class): object {
		$reflection = new \ReflectionClass($class);

		if (!$reflection->isInstantiable()) {
			throw new \Exception("Cannot instantiate $class");
		}

		$constructor = $reflection->getConstructor();
		if (is_null($constructor)) {
			return new $class();
		}

		$dependencies = $this->resolveParameters($constructor);
		return $reflection->newInstanceArgs($dependencies);
	}

	private function resolveParameters(\ReflectionMethod $constructor): array {
		$dependencies = [];

		foreach ($constructor->getParameters() as $param) {
			$type = $param->getType();

			if (!$type || $type->isBuiltin()) {
				throw new \Exception("Cannot resolve parameter {$param->getName()} in {$constructor->class}");
			}

			$dependencies[] = $this->get($type->getName());
		}

		return $dependencies;
	}

}