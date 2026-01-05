<?php

namespace Contexis\Events\Form\Infrastructure\Mappers;

use Contexis\Events\Form\Infrastructure\Contracts\DetailsMapper;
use Contexis\Events\Form\Domain\Contracts\FieldDetails;
use Contexis\Events\Form\Domain\Fields\NumberDetails;

class NumberMapper implements DetailsMapper
{
    public function map(array $attributes): FieldDetails
    {
        return new NumberDetails(
            placeholder: $attributes['placeholder'] ?? '',
            defaultValue: $attributes['defaultValue'] ?? null,
			min: $attributes['min'] ?? null,
			max: $attributes['max'] ?? null,
			step: $attributes['step'] ?? null,
			unit: $attributes['unit'] ?? null,
			variant: $attributes['variant'] ?? 'default'
        );
    }
}