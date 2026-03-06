<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\Fields;

use Contexis\Events\Form\Domain\Contracts\FieldDetails;
use Contexis\Events\Form\Domain\Enums\ValidationError;
use Contexis\Events\Form\Domain\Enums\FieldType;
use Contexis\Events\Form\Domain\ValueObjects\CountryCodes;

final class CountryDetails implements FieldDetails
{
	public function __construct(
		public readonly CountryCodes $countryCodes,
		public readonly string $defaultValue = '',
		public readonly string $placeholder = '',
		public readonly bool $hasNullOption = false,
	) {}

	public function getType(): FieldType
	{
		return FieldType::COUNTRY;
	}

	public function hydrate(mixed $value): mixed
	{
		return trim((string)$value);
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

	public function validateValue(mixed $value): ?ValidationError
	{
		if (!is_string($value)) {
			return ValidationError::INVALID_FORMAT;
		}
		return $this->countryCodes->contains($value)
			? null
			: ValidationError::INVALID_FORMAT;
	}

	public function isEmpty(mixed $value): bool
	{
		return $value === '';
	}
}
