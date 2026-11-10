<?php

declare(strict_types=1);

use Contexis\Events\Event\Application\DTOs\EventCalendarCriteria;
use Contexis\Events\Event\Infrastructure\EventPost;
use Contexis\Events\Event\Infrastructure\WpEventQueryBuilder;
use Contexis\Events\Shared\Domain\ValueObjects\Status;
use Contexis\Events\Shared\Domain\ValueObjects\StatusList;

it('queries the statuses allowed by the calendar criteria', function (): void {
    $criteria = new EventCalendarCriteria(
        startDate: new DateTimeImmutable('2026-11-01 00:00:00+01:00'),
        endDate: new DateTimeImmutable('2026-12-01 00:00:00+01:00'),
        statuses: StatusList::of(Status::Published, Status::Draft),
    );

    $args = WpEventQueryBuilder::fromCalendarCriteria($criteria)->getArgs();

    expect($args)->toMatchArray([
        'post_type' => EventPost::POST_TYPE,
        'post_status' => ['publish', 'draft'],
    ]);
});
