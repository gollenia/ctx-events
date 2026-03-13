<?php
declare(strict_types=1);

namespace Tests\Support;

namespace Tests\Support;

use Contexis\Events\Person\Domain\PersonRepository;
use Contexis\Events\Person\Domain\Person;
use Contexis\Events\Person\Domain\PersonCollection;
use Contexis\Events\Person\Domain\PersonId;
use Contexis\Events\Person\Domain\PersonCriteria;

final class FakePersonRepository implements PersonRepository
{
    public ?Person $toReturn;
    public ?PersonId $lastFindArg = null;
    public ?PersonCriteria $lastCriteria = null;

    public function __construct(?Person $toReturn = null)
    {
        $this->toReturn = $toReturn;
    }

    public function find(PersonId $id): ?Person
    {
        $this->lastFindArg = $id;
        return $this->toReturn;
    }

    public function search(PersonCriteria $criteria): PersonCollection
    {
        $this->lastCriteria = $criteria;
        return PersonCollection::from();
    }

    public function first(PersonCriteria $criteria): ?Person
    {
        $this->lastCriteria = $criteria;
        return $this->toReturn;
    }

    public function findByIds(array $ids): PersonCollection
    {
        return PersonCollection::empty();
    }

    public function get(PersonId $id): Person
    {
        $this->lastFindArg = $id;
        if ($this->toReturn === null) {
            throw new \RuntimeException("Person not found (mock)");
        }
        return $this->toReturn;
    }

    public function count(PersonCriteria $criteria): int
    {
        $this->lastCriteria = $criteria;
        return $this->toReturn ? 1 : 0;
    }
}
