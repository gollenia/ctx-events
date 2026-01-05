<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Infrastructure\Mappers;

use Contexis\Events\Form\Infrastructure\Contracts\DetailsMapper;
use Contexis\Events\Form\Domain\Fields\SelectDetails;
use Contexis\Events\Form\Domain\Contracts\FieldDetails;
use Contexis\Events\Form\Domain\Enums\SelectVariant;
use Contexis\Events\Form\Domain\ValueObjects\SelectOptions;

class SelectMapper implements DetailsMapper
{
    public function map(array $attributes): FieldDetails
    {
        return new SelectDetails(
			selectVariant: SelectVariant::from($attributes['selectVariant'] ?? 'default'),
            options: SelectOptions::fromArray($attributes['options'] ?? []),
            placeholder: $attributes['placeholder'] ?? '',
            hasNullOption: $attributes['hasNullOption'] ?? false,
            defaultValue: $attributes['defaultValue'] ?? null
        );
    }
}