<?php
declare(strict_types=1);

namespace Tests\Support;

namespace Tests\Support;

use Contexis\Events\Location\Domain\LocationRepository;
use Contexis\Events\Location\Domain\Location;
use Contexis\Events\Location\Domain\LocationCollection;
use Contexis\Events\Location\Domain\LocationId;
use Contexis\Events\Location\Domain\LocationCriteria;

final class FakeLocationRepository implements LocationRepository
{
    public ?Location $toReturn;
    public ?LocationId $lastFindArg = null;
    public ?LocationCriteria $lastCriteria = null;

    public function __construct(?Location $toReturn = null)
    {
        $this->toReturn = $toReturn;
    }

    public function find(?LocationId $id): ?Location
    {
        $this->lastFindArg = $id;
        return $this->toReturn;
    }

    public function search(LocationCriteria $criteria): LocationCollection
    {
        $this->lastCriteria = $criteria;
        return new LocationCollection();
    }

    public function first(LocationCriteria $criteria): ?Location
    {
        $this->lastCriteria = $criteria;
        return $this->toReturn;
    }

    public function findByIds(array $ids): LocationCollection
    {
        return new LocationCollection();
    }

    public function get(LocationId $id): Location
    {
        $this->lastFindArg = $id;
        if ($this->toReturn === null) {
            throw new \RuntimeException("Location not found (mock)");
        }
        return $this->toReturn;
    }

    public function count(LocationCriteria $criteria): int
    {
        $this->lastCriteria = $criteria;
        return $this->toReturn ? 1 : 0;
    }
}
