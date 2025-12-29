<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\ValueObjects;

use Contexis\Events\Form\Domain\Contracts\FieldDetails;

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

    public function validateValue(mixed $value): bool
    {
        return is_string($value);
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
