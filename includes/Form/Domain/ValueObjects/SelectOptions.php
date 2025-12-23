<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\ValueObjects;

final class SelectOptions
{
    public function __construct(
        public readonly array $options,
    ) {
    }

	public function contains(string $value): bool
    {
        return in_array($value, $this->options);
    }

	public function toArray(): array
    {
        return [
            'options' => $this->options,
        ];
    }
}