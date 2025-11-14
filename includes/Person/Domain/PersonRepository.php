<?php

namespace Contexis\Events\Person\Domain;

use Contexis\Events\Shared\Domain\ValueObjects\ViewContext;

interface PersonRepository
{
    public function find(PersonId $id): ?Person;
    public function search(PersonCriteria $criteria): PersonCollection;
    public function first(PersonCriteria $criteria): ?Person;
    public function get(PersonId $id): Person;
    public function count(PersonCriteria $criteria): int;
}
