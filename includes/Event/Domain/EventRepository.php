<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Event\Application\EventCriteria;

interface EventRepository
{
    public function find(?EventId $id): ?Event;
    public function get(?EventId $id): Event;
    public function first(EventCriteria $criteria): ?Event;
    public function search(EventCriteria $criteria): EventCollection;
    public function count(EventCriteria $criteria): int;
}
