<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\ValueObjects;

use Contexis\Events\Form\Domain\Contracts\FieldDetails;

final class InputField implements FieldDetails
{
    public function __construct(
		public readonly InputType $inputType,
        public readonly ?string $placeholder = null,
        public readonly ?string $defaultValue = null,
		public readonly ?string $pattern = null,
    ) {
    }

	public function getType(): FieldType
	{
		return FieldType::INPUT;
	}

	public function validateValue(mixed $value): bool
	{
		if ($this->pattern) {
			return preg_match($this->pattern, $value);
		}
		return match ($this->inputType) {
			InputType::TEXT => is_string($value),
			InputType::EMAIL => is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL),
			InputType::TEL => is_string($value) && preg_match('/^[0-9]{10}$/', $value),
			default => is_string($value),
		};
	}

	public function isEmpty(mixed $value): bool
	{
		return $value === '';
	}

	public function toArray(): array 
    {
        return [
			'type' => $this->getType()->value,
            'placeholder' => $this->placeholder,
            'defaultValue' => $this->defaultValue,
			'pattern' => $this->pattern,
        ];
    }
}
