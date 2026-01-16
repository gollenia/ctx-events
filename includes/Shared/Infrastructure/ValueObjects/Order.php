<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\ValueObjects;

enum Order: string
{
    case ASC = 'asc';
    case DESC = 'desc';

	public function fromString(string $value): self
	{
		$normalized = strtolower($value);
		if (!in_array($normalized, ['asc', 'desc'], true)) {
			throw new \InvalidArgumentException("Invalid order value: $value");
		}
		return self::from($normalized);
	}
}
