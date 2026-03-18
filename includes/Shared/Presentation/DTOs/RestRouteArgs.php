<?php

declare (strict_types=1);

namespace Contexis\Events\Shared\Presentation\DTOs;

final readonly class RestRouteArgs
{
	public function __construct(
		public string $namespace,
		public string $route
	) {}

	public static function from(string $namespace, string $route): self
	{
		return new self($namespace, $route);
	}
}