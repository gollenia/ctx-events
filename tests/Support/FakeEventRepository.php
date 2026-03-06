<?php

declare(strict_types=1);

namespace Tests\Support;

use Contexis\Events\Event\Application\DTOs\EventCriteria;
use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\EventCollection;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Shared\Application\ValueObjects\Pagination;

final class FakeEventRepository implements EventRepository
{
    /** @var array<int, Event> */
    private array $eventsById = [];

    public ?EventId $lastFindArg = null;
    public ?EventCriteria $lastCriteria = null;

    public function __construct(
        ?Event ...$events
    ) {
        foreach ($events as $event) {
            if ($event === null) {
                continue;
            }

            $this->eventsById[$event->id->toInt()] = $event;
        }
    }

    public static function empty(): self
    {
        return new self();
    }

    public static function one(Event $event): self
    {
        return new self($event);
    }

    public static function many(Event ...$events): self
    {
        return new self(...$events);
    }

    public function find(?EventId $id): ?Event
    {
        $this->lastFindArg = $id;

        if ($id === null) {
            return null;
        }

        return $this->eventsById[$id->toInt()] ?? null;
    }

    public function get(?EventId $id): Event
    {
        $event = $this->find($id);

        if ($event !== null) {
            return $event;
        }

        throw new \RuntimeException('Event not found (mock)');
    }

    public function first(EventCriteria $criteria): ?Event
    {
        $this->lastCriteria = $criteria;

        return array_values($this->eventsById)[0] ?? null;
    }

    public function search(EventCriteria $criteria): EventCollection
    {
        $this->lastCriteria = $criteria;

        $events = EventCollection::fromArray(array_values($this->eventsById));

        return $events->withPagination(Pagination::of(
            totalItems: count($this->eventsById),
            currentPage: $criteria->page,
            perPage: $criteria->perPage
        ));
    }

    public function count(EventCriteria $criteria): int
    {
        $this->lastCriteria = $criteria;

        return count($this->eventsById);
    }
}
