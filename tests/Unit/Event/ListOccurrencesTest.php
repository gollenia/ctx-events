<?php

declare(strict_types=1);

use Contexis\Events\Event\Application\DTOs\EventCriteria;
use Contexis\Events\Event\Application\Service\OccurrenceWindowFactory;
use Contexis\Events\Event\Application\Service\RecurringOccurrenceLister;
use Contexis\Events\Event\Application\UseCases\ListOccurrences;
use Contexis\Events\Event\Domain\Enums\EventStatus;
use Contexis\Events\Event\Domain\Enums\RecurrenceFrequency;
use Contexis\Events\Event\Domain\Enums\RecurrenceWeekday;
use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\EventCollection;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\OccurrenceExceptionCollection;
use Contexis\Events\Event\Domain\OccurrenceExceptionRepository;
use Contexis\Events\Event\Domain\OccurrenceGenerator;
use Contexis\Events\Event\Domain\OccurrenceCriteria;
use Contexis\Events\Event\Domain\RecurringEvent;
use Contexis\Events\Event\Domain\RecurringEventCollection;
use Contexis\Events\Event\Domain\RecurringEventRepository;
use Contexis\Events\Event\Domain\ValueObjects\EventFilters;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Domain\ValueObjects\EventViewConfig;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceWindow;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceId;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceIds;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceRule;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceWeekdays;
use Contexis\Events\Event\Domain\Enums\TimeScope;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\ValueObjects\AuthorId;
use Contexis\Events\Shared\Infrastructure\ValueObjects\OrderBy;

test('it merges real and virtual occurrences before applying pagination', function (): void {
    $timezone = new DateTimeZone('Europe/Vienna');
    $realStartsAt = new DateTimeImmutable('2026-01-06 09:00:00', $timezone);
    $virtualStartsAt = new DateTimeImmutable('2026-01-06 19:00:00', $timezone);
    $realEvent = new Event(
        id: new EventId(471),
        status: EventStatus::Published,
        name: 'Einzeltermin',
        startDate: $realStartsAt,
        endDate: $realStartsAt->modify('+1 hour'),
        createdAt: new DateTimeImmutable('2025-12-01', $timezone),
        eventViewConfig: new EventViewConfig(),
        authorId: new AuthorId(1),
    );
    $series = new RecurringEvent(
        id: new RecurrenceId(42),
        status: EventStatus::Published,
        name: 'Serientermin',
        recurrenceRule: new RecurrenceRule(
            $virtualStartsAt,
            3600,
            $timezone,
            RecurrenceFrequency::Weekly,
            weekdays: RecurrenceWeekdays::of(RecurrenceWeekday::Tuesday),
        ),
        createdAt: new DateTimeImmutable('2025-12-01', $timezone),
        authorId: new AuthorId(1),
    );

    $eventRepository = Mockery::mock(EventRepository::class);
    $eventRepository->shouldReceive('search')->once()->andReturn(EventCollection::from($realEvent));
    $seriesRepository = Mockery::mock(RecurringEventRepository::class);
    $seriesRepository->shouldReceive('search')->once()->andReturn(RecurringEventCollection::from($series));
    $exceptionRepository = Mockery::mock(OccurrenceExceptionRepository::class);
    $exceptionRepository->shouldReceive('findForRecurringEvents')->once()->andReturn(OccurrenceExceptionCollection::from());
    $clock = Mockery::mock(Clock::class);
    $clock->shouldReceive('now')->once()->andReturn(new DateTimeImmutable('2026-01-06 08:00:00', $timezone));

    $useCase = new ListOccurrences(
        $eventRepository,
        new RecurringOccurrenceLister($seriesRepository, $exceptionRepository, new OccurrenceGenerator()),
        new OccurrenceWindowFactory(),
        $clock,
    );

    $result = $useCase->execute(new EventCriteria(
        page: 1,
        perPage: 10,
        orderBy: OrderBy::default(),
        scope: TimeScope::TODAY,
        filters: EventFilters::empty(),
    ));

    expect($result->map(static fn ($item): string => $item->type))->toBe(['real', 'virtual'])
        ->and($result->map(static fn ($item): string => $item->occurrenceId))->toBe([
            'event:471',
            'recurrence:42:2026-01-06T19:00:00+01:00',
        ])
        ->and($result->pagination()?->totalItems)->toBe(2);
});
