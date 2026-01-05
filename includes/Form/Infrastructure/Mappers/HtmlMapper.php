<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Infrastructure\Mappers;

use Contexis\Events\Form\Infrastructure\Contracts\DetailsMapper;
use Contexis\Events\Form\Domain\Contracts\FieldDetails;
use Contexis\Events\Form\Domain\Fields\HtmlDetails;

class HtmlMapper implements DetailsMapper
{
    public function map(array $attributes): FieldDetails
    {
        return new HtmlDetails(
            content: $attributes['rendered_content'] ?? null
		);
    }
}