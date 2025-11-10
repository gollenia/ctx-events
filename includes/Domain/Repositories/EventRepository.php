<?php

namespace Contexis\Events\Domain\Repositories;

use Contexis\Events\Core\Contracts\QueryOptions;
use Contexis\Events\Domain\ValueObjects\Id\EventId;

interface EventRepository
{
    public function find(?EventId $id): ?\Contexis\Events\Domain\Models\Event;
    //public function query(QueryOptions $args): static;
    public function first(): ?\Contexis\Events\Domain\Models\Event;
    public function get(): \Contexis\Events\Domain\Collections\EventCollection;
    public function count(): int;
}
