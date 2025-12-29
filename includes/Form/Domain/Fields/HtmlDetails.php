<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\ValueObjects;

use Contexis\Events\Form\Domain\Contracts\FieldDetails;
use Contexis\Events\Form\Domain\Enums\FieldType;
use Contexis\Events\Form\Domain\Enums\ValidationError;

final readonly class HtmlDetails implements FieldDetails
{
    public function __construct(
        public readonly string $htmlContent
    ) {}

    public function getType(): FieldType
    {
        return FieldType::HTML;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->getType()->value,
            'content' => $this->htmlContent,
        ];
    }

    public function validateValue(mixed $value): ?ValidationError
    {
        return null;
    }

    public function hydrate(mixed $value): mixed
    {
        return $value;
    }

    public function isEmpty(mixed $value): bool
    {
        return true;
    }
}
