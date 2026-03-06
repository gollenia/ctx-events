<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\Fields;

use Contexis\Events\Form\Domain\Contracts\FieldDetails;
use Contexis\Events\Form\Domain\Enums\FieldType;
use Contexis\Events\Form\Domain\Enums\ValidationError;
use Contexis\Events\Form\Domain\Enums\InputType;

final class InputDetails implements FieldDetails
{
	public function __construct(
		public readonly InputType $inputType,
		public readonly ?string $placeholder = null,
		public readonly ?string $defaultValue = null,
		public readonly ?string $pattern = null,
	) {}

	public function getType(): FieldType
	{
		return FieldType::INPUT;
	}

	public function validateValue(mixed $value): ?ValidationError
	{
		if ($this->pattern) {
			return preg_match($this->pattern, $value)
				? null
				: ValidationError::INVALID_FORMAT;
		}
		return match ($this->inputType) {
			InputType::TEXT => is_string($value)
				? null
				: ValidationError::INVALID_FORMAT,
			InputType::EMAIL => is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL)
				? null
				: ValidationError::INVALID_FORMAT,
			InputType::TEL => is_string($value) && preg_match('/^[0-9]{10}$/', $value)
				? null
				: ValidationError::INVALID_FORMAT,
			default => is_string($value)
				? null
				: ValidationError::INVALID_FORMAT,
		};
	}

	public function isEmpty(mixed $value): bool
	{
		return $value === '';
	}

	public function hydrate(mixed $value): mixed
	{
		return trim((string)$value);
	}

	public function toArray(): array
	{
		return [
			'type' => $this->getType()->value,
			'inputType' => $this->inputType->value,
			'placeholder' => $this->placeholder,
			'defaultValue' => $this->defaultValue,
			'pattern' => $this->pattern,
		];
	}
}
