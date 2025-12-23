<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\ValueObjects;

use Contexis\Events\Form\Domain\Contracts\FieldDetails;
use Contexis\Events\Form\Domain\ValueObjects\NumberVariant;

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

    public function validateValue(mixed $value): bool
    {
        if (!is_numeric($value)) {
            return false;
        }

        $number = (int) $value;

        if ($this->min !== null && $number < $this->min) {
            return false;
        }

        if ($this->max !== null && $number > $this->max) {
            return false;
        }

        return true;
    }

    public function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '';
    }
}