<?php
declare(strict_types=1);

namespace Contexis\Events\Location\Domain;

use Contexis\Events\Shared\Domain\Abstract\Collection;

final readonly class LocationCollection extends Collection
{
    public function __construct(Location ...$locations)
    {
        parent::__construct($locations);
    }
}
