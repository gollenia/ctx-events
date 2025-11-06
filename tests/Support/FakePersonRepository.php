<?php

namespace Tests\Support;

use Contexis\Events\Domain\Repositories\PersonRepository;
use Contexis\Events\Domain\Models\Person;
use Contexis\Events\Domain\Collections\PersonCollection;
use Contexis\Events\Core\Contracts\QueryOptions;
use Contexis\Events\Domain\ValueObjects\Id\PersonId;

final class FakePersonRepository implements PersonRepository
{
    public ?Person $toReturn;
    public ?PersonId $lastFindArg = null;

    public function __construct(?Person $toReturn = null)
    {
        $this->toReturn = $toReturn;
    }

    /**
     * Interface: public function find(?PersonId $id): ?Person
     */
    public function find(?PersonId $id): ?Person
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
     * Interface: public function first(): ?Person
     */
    public function first(): ?Person
    {
        return $this->toReturn;
    }

    /**
     * Interface: public function get(): PersonCollection
     */
    public function get(): PersonCollection
    {
        // In Unit-Tests meist nicht nötig, deshalb nur Platzhalter:
        throw new \LogicException('get() not needed in FakePersonRepository');
    }

    /**
     * Interface: public function count(): int
     */
    public function count(): int
    {
        return $this->toReturn ? 1 : 0;
    }
}
