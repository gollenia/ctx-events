<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Domain\ValueObjects;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'PersonName')]
final class PersonName
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly ?string $prefix = '',
        public readonly ?string $suffix = ''
    ) {
    }

    public static function fromFullName(string $fullName): self
    {
        $parts = explode(' ', trim($fullName), 2);
        $firstName = $parts[0];
        $lastName = $parts[1] ?? '';

        return new self($firstName, $lastName);
    }

	public static function from(string $firstName, string $lastName, ?string $prefix = '', ?string $suffix = ''): self
	{
		return new self($firstName, $lastName, $prefix, $suffix);
	}

    public function getFullName(): string
    {
        $fullName = trim(($this->prefix ? $this->prefix . ' ' : '') . $this->firstName . ' ' . $this->lastName . ($this->suffix ? ' ' . $this->suffix : ''));
        return $fullName;
    }

    public function toString(): string
    {
        return $this->getFullName();
    }

	public function toArray(): array
	{
		return [
			'firstName' => $this->firstName,
			'lastName' => $this->lastName,
			'prefix' => $this->prefix,
			'suffix' => $this->suffix,
		];
	}
}
