<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Domain\ValueObjects;

final class RegistrationData
{
    public function __construct(
        public readonly array $data,
    ) {
    }

	public static function fromArray(array $data): self
	{
		return new self($data);
	}
}