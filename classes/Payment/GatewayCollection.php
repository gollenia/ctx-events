<?php

namespace Contexis\Events\Payment;

use Contexis\Events\Core\Container;

class GatewayCollection implements \IteratorAggregate, \Countable, \JsonSerializable {

	use \Contexis\Events\Core\Contracts\Application;

	private array $gateways = [];

	public function __construct() {
		foreach ($this->app()->get(GatewayService::class)->get_gateways() as $slug => $gateway) {
			$this->gateways[$slug] = $gateway;
		}
	}


	public static function all(): self {
		$instance = new self;
		return $instance;
	}

	public static function active(): self {
		$instance = new self;
		foreach ($instance->gateways as $key => $gateway) {
			if (!$gateway->is_active()) {
				unset($instance->gateways[$key]);
			}
		}
		return $instance;
	}

	public function jsonSerialize() : array {
		$fields = [];
		foreach ($this->gateways as $slug => $gateway) {
			$fields[] = $gateway->jsonSerialize();
		}
		return $fields;
	}

	public function get(string $slug): ?Gateway {
		if (array_key_exists($slug, $this->gateways)) {
			return $this->gateways[$slug];
		}
		return null;
	}

	public function list(): array {
		return array_keys($this->gateways);
	}

	public function has(string $slug): bool {
		return array_key_exists($slug, $this->gateways);
	}

	public function count(): int {
		return count($this->gateways);
	}

	public function getIterator(): \ArrayIterator {
		return new \ArrayIterator(array_values($this->gateways));
	}
}