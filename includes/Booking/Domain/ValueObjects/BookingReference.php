<?php

declare (strict_types=1);

namespace Contexis\Events\Booking\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class BookingReference
{
    public function __construct(
        private string $value
    ) {
		self::validate($value);
    }

	public static function fromString(string $reference): self
	{

		return new self($reference);
	}

	public static function fromParts(string $code, string $prefix = '', string $suffix = ''): self
	{
		$parts = array_filter([
            trim($prefix), 
            trim($code), 
            trim($suffix)
        ], fn($s) => $s !== '');
	
        return new self(strtoupper(implode('-', $parts)));
	}
	
    public function toString(): string
    {
        return $this->value;
    }

    private static function validate(string $reference): void
    {
		if (preg_match('/^[A-Z0-9-]{6,32}$/', $reference) !== 1) {
            throw new \InvalidArgumentException("Ungültige Referenz: $reference");
        } 
	}   
}