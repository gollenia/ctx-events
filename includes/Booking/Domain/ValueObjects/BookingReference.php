<?php

declare (strict_types=1);

namespace Contexis\Events\Booking\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class BookingReference
{
    public function __construct(
        private string $value
    ) {
        if (!self::isValid($value)) {
            throw new InvalidArgumentException('Invalid Reference.');
        }
    }

	public static function fromString(string $reference): self
	{

		return new self($reference);
	}

    public function toString(): string
    {
        return $this->value;
    }

    private static function isValid(string $reference): bool
    {
        return preg_match('/^[A-Za-z0-9]{12}$/', $reference) === 1;
    }
}