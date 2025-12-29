<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\ValueObjects;

final readonly class ValidationResult
{
    private function __construct(
        public bool $isValid,
        public array $validatedData,
        public array $errors
    ) {}

    public static function valid(array $data): self
    {
        return new self(true, $data, []);
    }

    public static function invalid(array $errors): self
    {
        return new self(false, [], $errors);
    }
}
