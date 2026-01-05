<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Infrastructure\Mappers;

use Contexis\Events\Form\Infrastructure\Contracts\DetailsMapper;
use Contexis\Events\Form\Domain\Contracts\FieldDetails;
use Contexis\Events\Form\Domain\Fields\TextareaDetails;

class TextareaMapper implements DetailsMapper
{
    public function map(array $attributes): FieldDetails
    {
        return new TextareaDetails(
            placeholder: $attributes['placeholder'] ?? '',
            defaultValue: $attributes['defaultValue'] ?? null,
            rows: $attributes['rows'] ?? 3
        );
    }
}