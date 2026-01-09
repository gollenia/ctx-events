<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Event\Application\EventCriteria;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Domain\ValueObjects\EventSpaces;
use Contexis\Events\Shared\Domain\ValueObjects\Price;

interface EventRepository
{
    public function find(?EventId $id): ?Event;
    public function get(?EventId $id): Event;
    public function first(EventCriteria $criteria): ?Event;
    public function search(EventCriteria $criteria): EventCollection;
    public function count(EventCriteria $criteria): int;
	public function save(Event $event): void;
}
