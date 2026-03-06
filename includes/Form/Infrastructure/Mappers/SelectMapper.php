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
		$rawVariant = $attributes['selectVariant'] ?? null;
		$normalizedVariant = is_string($rawVariant) ? strtolower(trim($rawVariant)) : null;

		$selectVariant = match ($normalizedVariant) {
			'default', '', null => SelectVariant::SELECT,
			default => SelectVariant::tryFrom($normalizedVariant) ?? SelectVariant::SELECT,
		};

        return new SelectDetails(
			selectVariant: $selectVariant,
            options: SelectOptions::fromArray($attributes['options'] ?? []),
            placeholder: $attributes['placeholder'] ?? '',
            hasNullOption: $attributes['hasNullOption'] ?? false,
            defaultValue: $attributes['defaultValue'] ?? null
        );
    }
}