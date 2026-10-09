<?php

declare(strict_types=1);

use Contexis\Events\Event\Domain\Enums\EventStatus;
use Contexis\Events\Event\Domain\OccurrenceCriteria;
use Contexis\Events\Event\Domain\ValueObjects\EventFilters;
use Contexis\Events\Event\Domain\ValueObjects\EventStatusList;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceWindow;
use Contexis\Events\Event\Infrastructure\EventTaxonomy;
use Contexis\Events\Event\Infrastructure\RecurringEventMeta;
use Contexis\Events\Event\Infrastructure\RecurringEventPost;
use Contexis\Events\Event\Infrastructure\WpRecurringEventQueryBuilder;

it('queries recurring series by shared content filters without comparing local recurrence timestamps', function (): void {
    $criteria = new OccurrenceCriteria(
        window: new OccurrenceWindow(
            new DateTimeImmutable('2026-11-01 00:00:00+01:00'),
            new DateTimeImmutable('2026-12-01 00:00:00+01:00'),
        ),
        filters: new EventFilters(categories: [7], tags: [9], locationId: 3, personId: 5, search: 'Choir'),
        statuses: EventStatusList::of(EventStatus::Published, EventStatus::Cancelled),
    );

    $args = WpRecurringEventQueryBuilder::fromCriteria($criteria)->getArgs();

    expect($args)->toMatchArray([
        'post_type' => RecurringEventPost::POST_TYPE,
        'post_status' => ['publish', 'cancelled'],
        'posts_per_page' => -1,
        'no_found_rows' => true,
        's' => 'Choir',
    ]);
    expect($args['tax_query'])->toContain([
        'taxonomy' => EventTaxonomy::CATEGORIES,
        'field' => 'term_id',
        'terms' => [7],
    ]);
    expect($args['tax_query'])->toContain([
        'taxonomy' => EventTaxonomy::TAGS,
        'field' => 'term_id',
        'terms' => [9],
    ]);
    expect($args['meta_query'])->toContain([
        'key' => RecurringEventMeta::LOCATION_ID,
        'value' => '3',
        'compare' => '=',
        'type' => 'CHAR',
    ]);
    expect($args['meta_query'])->toContain([
        'key' => RecurringEventMeta::PERSON_ID,
        'value' => '5',
        'compare' => '=',
        'type' => 'CHAR',
    ]);
    expect($args['meta_query'])->toHaveCount(3);
});
