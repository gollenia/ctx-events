<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\ValueObjects;

use Contexis\Events\Form\Domain\Contracts\FieldDetails;
use Contexis\Events\Form\Domain\Enums\ValidationError;
use Contexis\Events\Form\Domain\Enums\SelectVariant;
use Contexis\Events\Form\Domain\Enums\FieldType;

final class SelectDetails implements FieldDetails
{
	public function __construct(
		public readonly SelectVariant $selectVariant,
		public readonly SelectOptions $options,
		public readonly string $placeholder = '',
		public readonly bool $hasNullOption = false,
		public readonly ?string $defaultValue = null,
	) {}

	public function getType(): FieldType
	{
		return FieldType::SELECT;
	}

	public function validateValue(mixed $value): ?ValidationError
	{
		if (!is_string($value) && !is_int($value)) {
			return ValidationError::INVALID_FORMAT;
		}

		return $this->options->contains($value)
			? null
			: ValidationError::INVALID_FORMAT;
	}

	public function isEmpty(mixed $value): bool
	{
		return $value === '';
	}

	public function hydrate(mixed $value): mixed
	{
		return (string) $value;
	}

	public function toArray(): array
	{
		return [
			'type' => $this->getType()->value,
			'selectVariant' => $this->selectVariant->value,
			'options' => $this->options->toArray(),
			'hasNullOption' => $this->hasNullOption,
			'placeholder' => $this->placeholder,
			'defaultValue' => $this->defaultValue,
		];
	}
}
