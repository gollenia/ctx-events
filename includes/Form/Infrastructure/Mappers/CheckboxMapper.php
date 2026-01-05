<?php

namespace Contexis\Events\Form\Infrastructure\Mappers;

use Contexis\Events\Form\Infrastructure\Contracts\DetailsMapper;
use Contexis\Events\Form\Domain\Fields\CheckboxDetails;
use Contexis\Events\Form\Domain\Contracts\FieldDetails;

class CheckboxMapper implements DetailsMapper
{
    public function map(array $attributes): FieldDetails
    {
        return new CheckboxDetails(
            defaultValue: $attributes['defaultValue'] ?? false,
            requiredMessage: $attributes['requiredMessage'] ?? '',
            variant: $attributes['variant'] ?? 'default'
        );
    }
}