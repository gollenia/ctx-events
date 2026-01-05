<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Infrastructure\Mappers;

use Contexis\Events\Form\Infrastructure\Contracts\DetailsMapper;
use Contexis\Events\Form\Domain\Fields\InputDetails;
use Contexis\Events\Form\Domain\Contracts\FieldDetails;
use Contexis\Events\Form\Domain\Enums\InputType;

class InputMapper implements DetailsMapper
{
    public function map(array $attributes): FieldDetails
    {
        return new InputDetails(
            inputType: InputType::from($attributes['inputType'] ?? 'text'),
            placeholder: $attributes['placeholder'] ?? '',
            defaultValue: $attributes['defaultValue'] ?? null,
            pattern: $attributes['pattern'] ?? null,
        );
    }
}