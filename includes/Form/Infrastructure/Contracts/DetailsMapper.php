<?php

namespace Contexis\Events\Form\Infrastructure\Contracts;

use Contexis\Events\Form\Domain\Contracts\FieldDetails;

interface DetailsMapper
{
    public function map(array $attributes): FieldDetails;
}