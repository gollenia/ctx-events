<?php

namespace Contexis\Events\Domain\Repositories;

use Contexis\Events\Core\Contracts\QueryOptions;
use Contexis\Events\Domain\ValueObjects\Id\PersonId;

interface PersonRepository
{
    public function find(?PersonId $id): ?\Contexis\Events\Domain\Models\Person;
    public function query(QueryOptions $args): static;
    public function first(): ?\Contexis\Events\Domain\Models\Person;
    public function get(): \Contexis\Events\Domain\Collections\PersonCollection;
    public function count(): int;
}
