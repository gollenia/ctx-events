<?php

declare(strict_types=1);

use Contexis\Events\Event\Application\Service\RecurringOccurrenceLister;
use Contexis\Events\Event\Domain\Enums\EventStatus;
use Contexis\Events\Event\Domain\Enums\RecurrenceFrequency;
use Contexis\Events\Event\Domain\Enums\RecurrenceWeekday;
use Contexis\Events\Event\Domain\OccurrenceException;
use Contexis\Events\Event\Domain\OccurrenceExceptionCollection;
use Contexis\Events\Event\Domain\OccurrenceExceptionRepository;
use Contexis\Events\Event\Domain\OccurrenceCriteria;
use Contexis\Events\Event\Domain\OccurrenceGenerator;
use Contexis\Events\Event\Domain\Occurrence;
use Contexis\Events\Event\Domain\RecurringEvent;
use Contexis\Events\Event\Domain\RecurringEventCollection;
use Contexis\Events\Event\Domain\RecurringOccurrence;
use Contexis\Events\Event\Domain\RecurringEventRepository;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Domain\ValueObjects\EventFilters;
use Contexis\Events\Event\Domain\ValueObjects\EventStatusList;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceKey;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceWindow;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceId;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceIds;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceRule;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceWeekdays;
use Contexis\Events\Shared\Domain\ValueObjects\AuthorId;

function recurringOccurrenceTimezone(): DateTimeZone
{
    return new DateTimeZone('Europe/Vienna');
}

function recurringOccurrenceWindow(): OccurrenceWindow
{
    $timezone = recurringOccurrenceTimezone();

    return new OccurrenceWindow(
        new DateTimeImmutable('2026-01-01', $timezone),
        new DateTimeImmutable('2026-01-22', $timezone),
    );
}

function recurringOccurrenceCriteria(): OccurrenceCriteria
{
    return new OccurrenceCriteria(
        recurringOccurrenceWindow(),
        EventFilters::empty(),
        EventStatusList::public(),
    );
}

function recurringEventForOccurrences(int $id, string $firstStartsAt): RecurringEvent
{
    $timezone = recurringOccurrenceTimezone();

    return new RecurringEvent(
        new RecurrenceId($id),
        EventStatus::Published,
        'Serie ' . $id,
        new RecurrenceRule(
            new DateTimeImmutable($firstStartsAt, $timezone),
            3600,
            $timezone,
            RecurrenceFrequency::Weekly,
            weekdays: RecurrenceWeekdays::of(RecurrenceWeekday::Tuesday),
        ),
        new DateTimeImmutable('2025-12-01', $timezone),
        new AuthorId(1),
    );
}

final class InMemoryRecurringEventRepository implements RecurringEventRepository
{
    public function __construct(private readonly RecurringEventCollection $events)
    {
    }

    public function search(OccurrenceCriteria $criteria): RecurringEventCollection
    {
        return $this->events;
    }

    public function find(RecurrenceId $id): ?RecurringEvent
    {
        foreach ($this->events as $event) {
            if ($event->id->equals($id)) {
                return $event;
            }
        }

        return null;
    }
}

final class InMemoryOccurrenceExceptionRepository implements OccurrenceExceptionRepository
{
    public ?RecurrenceIds $requestedIds = null;

    public function __construct(private readonly OccurrenceExceptionCollection $exceptions)
    {
    }

    public function findForRecurringEvents(RecurrenceIds $recurringEventIds, OccurrenceWindow $window): OccurrenceExceptionCollection
    {
        $this->requestedIds = $recurringEventIds;

        return $this->exceptions;
    }
}

test('lists virtual occurrences from every matching recurring event in chronological order', function () {
    $repository = new InMemoryRecurringEventRepository(RecurringEventCollection::from(
        recurringEventForOccurrences(42, '2026-01-06 19:00:00'),
        recurringEventForOccurrences(43, '2026-01-06 09:00:00'),
    ));
    $exceptions = new InMemoryOccurrenceExceptionRepository(OccurrenceExceptionCollection::from());
    $lister = new RecurringOccurrenceLister($repository, $exceptions, new OccurrenceGenerator());

    $items = $lister->list(recurringOccurrenceCriteria());

    expect($items->count())->toBe(6)
        ->and($items->first()?->occurrence->startsAt->format('Y-m-d H:i'))->toBe('2026-01-06 09:00')
        ->and($items->first()?->recurringEvent->id->toInt())->toBe(43)
        ->and($exceptions->requestedIds?->contains(new RecurrenceId(42)))->toBeTrue()
        ->and($exceptions->requestedIds?->contains(new RecurrenceId(43)))->toBeTrue();
});

test('omits cancelled and detached slots from recurring occurrences', function () {
    $event = recurringEventForOccurrences(42, '2026-01-06 19:00:00');
    $timezone = recurringOccurrenceTimezone();
    $cancelledKey = OccurrenceKey::from(new RecurrenceId(42), new DateTimeImmutable('2026-01-13 19:00:00', $timezone));
    $detachedKey = OccurrenceKey::from(new RecurrenceId(42), new DateTimeImmutable('2026-01-20 19:00:00', $timezone));
    $exceptions = new InMemoryOccurrenceExceptionRepository(OccurrenceExceptionCollection::from(
        OccurrenceException::cancelled($cancelledKey),
        OccurrenceException::detached($detachedKey, new EventId(471)),
    ));
    $lister = new RecurringOccurrenceLister(
        new InMemoryRecurringEventRepository(RecurringEventCollection::from($event)),
        $exceptions,
        new OccurrenceGenerator(),
    );

    $items = $lister->list(recurringOccurrenceCriteria());

    expect($items->count())->toBe(1)
        ->and($items->first()?->occurrence->startsAt->format('Y-m-d H:i'))->toBe('2026-01-06 19:00');
});

test('a recurring occurrence cannot wrap a detached individual event', function () {
    $event = recurringEventForOccurrences(42, '2026-01-06 19:00:00');
    $timezone = recurringOccurrenceTimezone();
    $startsAt = new DateTimeImmutable('2026-01-13 19:00:00', $timezone);
    $key = OccurrenceKey::from(new RecurrenceId(42), $startsAt);

    expect(fn () => new RecurringOccurrence(
        $event,
        Occurrence::detached($key, $startsAt, $startsAt->modify('+1 hour'), new EventId(471)),
    ))->toThrow(InvalidArgumentException::class);
});
