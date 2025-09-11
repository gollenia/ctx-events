<?php

namespace Contexis\Events\Core\Utilities;

use JsonSerializable;
use WP_REST_Response;

class ValidationResult implements JsonSerializable {
	public function __construct(
		public readonly bool $valid,
		public readonly ?string $code = null,
		public readonly ?string $message = null
	) {}

	public static function success(): self {
		return new self(true);
	}

	public static function fail(string $code, string $message): self {
		return new self(false, $code, $message);
	}

	public function to_response(): WP_REST_Response {
		return new \WP_REST_Response($this, $this->valid ? 200 : 400);
	}

	public function jsonSerialize(): mixed {
		return [
			'valid' => $this->valid,
			'code' => $this->code,
			'message' => $this->message
		];
	}
}