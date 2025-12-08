<?php
declare(strict_types=1);

namespace Contexis\Events\Location\Domain;

interface LocationRepository
{
    public function find(?LocationId $id): ?Location;
    public function search(LocationCriteria $args): LocationCollection;
    public function findByIds(array $ids): LocationCollection;
    public function first(LocationCriteria $criteria): ?Location;
    public function get(LocationId $id): Location;
    public function count(LocationCriteria $criteria): int;
}
