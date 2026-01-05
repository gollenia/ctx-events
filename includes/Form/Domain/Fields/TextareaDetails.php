<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\Fields;

use Contexis\Events\Form\Domain\Contracts\FieldDetails;
use Contexis\Events\Form\Domain\Enums\FieldType;
use Contexis\Events\Form\Domain\Enums\ValidationError;

final readonly class TextareaDetails implements FieldDetails
{
    public function __construct(
		public readonly int $rows,
        public readonly ?string $placeholder = null,
        public readonly ?string $defaultValue = null,
    ) {}

    public function getType(): FieldType
    {
        return FieldType::TEXTAREA;
    }

    public function validateValue(mixed $value): ?ValidationError
    {
        return is_string($value)
            ? null
            : ValidationError::INVALID_FORMAT;
    }

	public function hydrate(mixed $value): mixed
	{
		return (string) $value;
	}

    public function isEmpty(mixed $value): bool
    {
        return $value === '';
    }

    public function toArray(): array
    {
        return [
            'type' => $this->getType()->value,
            'rows' => $this->rows,
            'placeholder' => $this->placeholder,
            'defaultValue' => $this->defaultValue,
        ];
    }
}
