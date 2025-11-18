<?php

namespace Contexis\Events\Shared\Domain\ValueObjects;

final class Email
{
    public function __construct(
        private readonly string $address
    ) {
    }

    public static function tryFrom(?string $address): ?self
    {
        if ($address === null) return null;

        $address = trim($address);

        if ($address === '') return null;

        if (!filter_var($address, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return new self($address);
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
