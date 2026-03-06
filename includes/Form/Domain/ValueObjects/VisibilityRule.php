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
        $actual = $this->normalize($allFormData[$this->dependsOnField] ?? null);
        $expected = $this->normalize($this->expectedValue);

        return match ($this->operator) {
            'equals'     => $actual == $expected,
            'not_equals' => $actual != $expected,
            'not_empty'  => $actual !== null && $actual !== '' && $actual !== false,
            default      => false,
        };
    }

    private function normalize(mixed $value): mixed
    {
        if (in_array($value, ['checked', 'on', '1', 1, true], true)) return true;
        if (in_array($value, ['unchecked', 'off', '0', 0, false], true)) return false;
        return $value;
    }
}
