<?php

namespace Contexis\Events\Domain\ValueObjects;

final class Email
{
    public function __construct(
        private readonly string $address
    ) {
    }

    public function address(): string
    {
        return $this->address;
    }

    public function __toString(): string
    {
        return $this->address;
    }

    public function isValid(): bool
    {
        return filter_var($this->address, FILTER_VALIDATE_EMAIL) !== false;
    }
}
