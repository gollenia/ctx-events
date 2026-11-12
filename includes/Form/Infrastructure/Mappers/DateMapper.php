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
            defaultValue: $this->date($attributes['defaultValue'] ?? null),
			placeholder: null,
			earliestDate: $this->date($attributes['min'] ?? null),
			latestDate: $this->date($attributes['max'] ?? null),
			useEventAgeLimits: (bool) ($attributes['useEventAgeLimits'] ?? false),
		);
    }

    private function date(mixed $value): ?\DateTimeImmutable
    {
        if (!is_string($value) || $value === '') {
            return null;
        }

        return \DateTimeImmutable::createFromFormat('!Y-m-d', $value) ?: null;
    }
}
