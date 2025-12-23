<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\ValueObjects;

final readonly class VisibilityRule
{
    public function __construct(
        public string $dependsOnField,
        public mixed $expectedValue,
        public string $operator = 'equals' 
    ) {}

    public function toArray(): array
    {
        return [
            'field' => $this->dependsOnField,
            'value' => $this->expectedValue,
            'operator' => $this->operator,
        ];
    }
    
    public function isMet(array $allFormData): bool
    {
        $actualValue = $allFormData[$this->dependsOnField] ?? null;

        return match ($this->operator) {
            'equals' => $actualValue == $this->expectedValue,
            'not_equals' => $actualValue != $this->expectedValue,
            default => false,
        };
    }
}