<?php

namespace Contexis\Events\Core;


class Request {
	private \Symfony\Component\HttpFoundation\Request $request;

	public function __construct(\Symfony\Component\HttpFoundation\Request $request) {
		$this->request = $request;
	}

	public function string(string $key, string $default = ''): string {
		return trim((string) $this->request->get($key, $default));
	}

	public function int(string $key, int $default = 0): int {
		return is_numeric($this->request->get($key)) ? intval($this->request->get($key)) : $default;
	}

	public function bool(string $key, bool $default = false): bool {
		$val = strtolower((string) $this->request->get($key, $default ? '1' : '0'));
		return in_array($val, ['1', 'true', 'yes', 'on'], true);
	}

	public function array(string $key, array $default = []): array {
		$val = $this->request->get($key, $default);
		if (is_array($val)) {
			return $val;
		}
		$array = explode(',', $val);
		if(empty($array) || !is_array($array)) {
			return $default;
		}
		return $array;
	}

	public function has(string $key): bool {
		return $this->request->get($key) !== null;
	}

	public function raw(): \Symfony\Component\HttpFoundation\Request {
		return $this->request;
	}
}