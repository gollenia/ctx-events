<?php

namespace Contexis\Events\Location\Domain;

use Contexis\Events\Shared\Application\Contracts\Criteria;

interface LocationRepository
{
    public function find(?LocationId $id): ?Location;
    public function search(LocationCriteria $args): LocationCollection;
    public function first(LocationCriteria $criteria): ?Location;
    public function get(LocationId $id): Location;
    public function count(LocationCriteria $criteria): int;
}
