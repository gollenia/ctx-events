<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Domain\ValueObjects;

final class RegistrationData
{
	private array $data;
    public function __construct(
       array $data 
    ) {
		$this->data = $data;
    }

	public function all(): array
    {
        return $this->data;
    }

	public function getString(string $key): ?string
    {
        $value = $this->data[$key] ?? null;
        if ($value === null) return null;
        if (is_string($value)) return $value;
        if (is_scalar($value)) return (string) $value;
        return null;
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}