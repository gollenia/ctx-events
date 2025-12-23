<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\ValueObjects;

use Contexis\Events\Form\Domain\Contracts\FieldDetails;

final class CountryDetails implements FieldDetails
{
    public function __construct(
        public readonly string $defaultValue = '',
		public readonly string $placeholder = '',
		public readonly CountryCodes $countryCodes,
		public readonly bool $hasNullOption = false,
    ) {
    }

	public function getType(): FieldType
	{
		return FieldType::COUNTRY;
	}

    public function toArray(): array
    {
		return [
			'type' => $this->getType()->value,
			'default' => $this->defaultValue,
			'hasNullOption' => $this->hasNullOption,
			'countryCodes' => $this->countryCodes->toArray(),
			'placeholder' => $this->placeholder,
		];
    }

    public function validateValue(mixed $value): bool
    {
		if (!is_string($value)) {
			return false;
		}
		return $this->countryCodes->contains($value);
    }

	public function isEmpty(mixed $value): bool
	{
		return $value === '';
	}
}
