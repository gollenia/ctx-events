<?php

namespace Contexis\Events\Domain\Repositories;

use Contexis\Events\Core\Contracts\QueryOptions;
use Contexis\Events\Domain\ValueObjects\Id\LocationId;

interface LocationRepository
{
    public function find(?LocationId $id): ?\Contexis\Events\Domain\Models\Location;
    public function query(QueryOptions $args): static;
    public function first(): ?\Contexis\Events\Domain\Models\Location;
    public function get(): \Contexis\Events\Domain\Collections\LocationCollection;
    public function count(): int;
}
