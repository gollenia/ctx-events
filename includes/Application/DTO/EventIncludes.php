<?php

namespace Contexis\Events\Application\DTO;

use Contexis\Events\Application\DTO as DTO;

class EventIncludes
{
    public function __construct(
        public readonly ?DTO\Location $location = null,
        public readonly ?DTO\Image $image = null
    ) {
    }
}
