<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Domain\ValueObjects;

use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;

final readonly class RegistrationData implements \JsonSerializable
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

	public function requireEmail(): Email
	{
		$email = $this->getString('email');
		if ($email === null) {
			throw new \DomainException('Email is required in registration data.');
		}
		return Email::tryFrom($email);
	}

	public function requirePersonName(): PersonName
	{
		$first_name = $this->getString('first_name');
		$last_name = $this->getString('last_name');
		if ($first_name === null || $last_name === null) {
			throw new \DomainException('First name and last name are required in registration data.');
		}
		return new PersonName($first_name, $last_name);
	}

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}