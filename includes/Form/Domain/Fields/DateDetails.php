<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\Fields;

use Contexis\Events\Form\Domain\Contracts\FieldDetails;
use Contexis\Events\Form\Domain\Enums\FieldType;
use Contexis\Events\Form\Domain\Enums\ValidationError;
use DateTimeImmutable;
use Exception;

class DateDetails implements FieldDetails
{
    public function __construct(
        public readonly ?DateTimeImmutable $defaultValue = null,
        public readonly ?DateTimeImmutable $placeholder = null,
        public readonly ?DateTimeImmutable $earliestDate = null,
        public readonly ?DateTimeImmutable $latestDate = null,
    ) {}

    public function getType(): FieldType
    {
        return FieldType::DATE;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->getType()->value,
            'default' => $this->defaultValue?->format('Y-m-d'),
            'min' => $this->earliestDate?->format('Y-m-d'),
            'max' => $this->latestDate?->format('Y-m-d'),
        ];
    }

    public function validateValue(mixed $value): ?ValidationError
    {
        $dateValue = $this->tryParse($value);

        if ($dateValue === null) {
            return ValidationError::INVALID_FORMAT;
        }

        if ($this->earliestDate && $dateValue < $this->earliestDate) {
            return ValidationError::TOO_LOW;
        }

        if ($this->latestDate && $dateValue > $this->latestDate) {
            return ValidationError::TOO_HIGH;
        }

        return null;
    }

    public function hydrate(mixed $value): mixed
    {
        return (new DateTimeImmutable($value))->setTime(0, 0, 0);
    }

    public function isEmpty(mixed $value): bool
    {
        return $value === '';
    }

    private function tryParse(mixed $value): ?DateTimeImmutable
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Exception) {
            return null;
        }
    }
}
