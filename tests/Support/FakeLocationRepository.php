<?php

namespace Tests\Support;

use Contexis\Events\Domain\Repositories\LocationRepository;
use Contexis\Events\Domain\Models\Location;
use Contexis\Events\Domain\Collections\LocationCollection;
use Contexis\Events\Core\Contracts\QueryOptions;
use Contexis\Events\Domain\ValueObjects\Id\LocationId;

final class FakeLocationRepository implements LocationRepository
{
    public ?Location $toReturn;
    public ?LocationId $lastFindArg = null;

    public function __construct(?Location $toReturn = null)
    {
        $this->toReturn = $toReturn;
    }

    /**
     * Interface: public function find(?LocationId $id): ?Location
     */
    public function find(?LocationId $id): ?Location
    {
        $this->lastFindArg = $id;
        return $this->toReturn;
    }

    /**
     * Interface: public function query(QueryOptions $args): static
     */
    public function query(QueryOptions $args): static
    {
        return $this;
    }

    /**
     * Interface: public function first(): ?Location
     */
    public function first(): ?Location
    {
        return $this->toReturn;
    }

    /**
     * Interface: public function get(): LocationCollection
     */
    public function get(): LocationCollection
    {
        // In Unit-Tests meist nicht nötig, deshalb nur Platzhalter:
        throw new \LogicException('get() not needed in FakeLocationRepository');
    }

    /**
     * Interface: public function count(): int
     */
    public function count(): int
    {
        return $this->toReturn ? 1 : 0;
    }
}
