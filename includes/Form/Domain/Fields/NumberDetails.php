<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\Fields;

use Contexis\Events\Form\Domain\Contracts\FieldDetails;
use Contexis\Events\Form\Domain\Enums\FieldType;
use Contexis\Events\Form\Domain\Enums\ValidationError;
use Contexis\Events\Form\Domain\Enums\NumberVariant;

final readonly class NumberDetails implements FieldDetails
{
    public function __construct(
        public ?int $min = null,
        public ?int $max = null,
        public ?int $step = 1,
        public NumberVariant $variant = NumberVariant::INPUT,
        public int|float|null $defaultValue = null,
        public ?string $placeholder = null,
        public ?string $unit = null,
    ) {}

    public function getType(): FieldType
    {
        return FieldType::NUMBER;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->getType()->value,
            'variant' => $this->variant->value,
            'min' => $this->min,
            'max' => $this->max,
            'step' => $this->step,
            'default' => $this->defaultValue,
            'placeholder' => $this->placeholder,
            'unit' => $this->unit,
        ];
    }

    public function validateValue(mixed $value): ?ValidationError
    {
        if (!is_numeric($value)) {
            return ValidationError::INVALID_FORMAT;
        }

        $number = (int) $value;

        if ($this->min !== null && $number < $this->min) {
            return ValidationError::TOO_LOW;
        }

        if ($this->max !== null && $number > $this->max) {
            return ValidationError::TOO_HIGH;
        }

        return null;
    }

    public function hydrate(mixed $value): mixed
    {
        return (int) $value;
    }

    public function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '';
    }
}
