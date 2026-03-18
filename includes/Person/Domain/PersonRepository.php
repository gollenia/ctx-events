<?php
declare(strict_types=1);

namespace Contexis\Events\Person\Domain;

interface PersonRepository
{
    public function find(PersonId $id): ?Person;
    public function search(PersonCriteria $criteria): PersonCollection;
    public function first(PersonCriteria $criteria): ?Person;
	/*	 
	* @param array<PersonId> $ids
	 */
    public function findByIds(array $ids): PersonCollection;
    public function get(PersonId $id): Person;
    public function count(PersonCriteria $criteria): int;
}
