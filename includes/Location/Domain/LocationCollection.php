<?php

namespace Contexis\Events\Location\Domain;

use Contexis\Events\Shared\Domain\Abstract\Collection;

final class LocationCollection extends Collection
{
    public function __construct(Location ...$locations)
    {
        $this->items = $locations;
    }
}
