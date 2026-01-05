<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Infrastructure\Mappers;

use Contexis\Events\Form\Infrastructure\Contracts\DetailsMapper;
use Contexis\Events\Form\Domain\Contracts\FieldDetails;
use Contexis\Events\Form\Domain\Fields\DateDetails;

class DateMapper implements DetailsMapper
{
    public function map(array $attributes): FieldDetails
    {
        return new DateDetails(
            defaultValue: $attributes['defaultValue'] ?? null,
			placeholder: $attributes['placeholder'] ?? '',
			earliestDate: $attributes['earliestDate'] ?? null,
			latestDate: $attributes['latestDate'] ?? null
		);
    }
}