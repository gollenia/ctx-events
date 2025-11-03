<?php

namespace Contexis\Events\Core;

use DI\ContainerBuilder;

final class App {
	private static ?\DI\Container $container = null;

	public static function get_container() {
		if(self::$container !== null) {
			return self::$container;
		}
	
		$builder = new \DI\ContainerBuilder();
	
		$definitions = require __DIR__ . '/config/container.php';
		
		if (!\is_array($definitions)) {
			throw new \RuntimeException("DI definitions file must return array, got " . gettype($definitions));
		}

		$builder->addDefinitions($definitions);

		self::$container = $builder->build();

		return self::$container;
	}

	public static function set(string $id, mixed $value): void {
		self::get_container()->set($id, $value);
	}

	public static function get(string $id): mixed {
		return self::get_container()->get($id);
	}

	public static function has(string $id): bool {
		return self::get_container()->has($id);
	}

	public static function make(string $id, array $parameters = []): mixed {
		return self::get_container()->make($id, $parameters);
	}

	public static function callable(callable $callable, array $parameters = []): mixed {
		return self::get_container()->call($callable, $parameters);
	}

	public static function inject_on(object $object): void {
		self::get_container()->injectOn($object);
	}
}