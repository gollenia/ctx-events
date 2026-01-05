<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Infrastructure\Mappers;

use Contexis\Events\Form\Infrastructure\Contracts\DetailsMapper;
use Contexis\Events\Form\Domain\Fields\CountryDetails;
use Contexis\Events\Form\Domain\Contracts\FieldDetails;
use Contexis\Events\Form\Domain\ValueObjects\CountryCodes;

class CountryMapper implements DetailsMapper
{
    public function map(array $attributes): FieldDetails
    {
        return new CountryDetails(
            defaultValue: $attributes['defaultValue'] ?? null,
			placeholder: $attributes['placeholder'] ?? '',
			hasNullOption: $attributes['hasNullOption'] ?? false,
			countryCodes: CountryCodes::of($attributes['allowedCountries'] ?? [])
        );
    }
}