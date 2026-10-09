<?php

declare(strict_types=1);

use Contexis\Events\Event\Domain\ValueObjects\EventFilters;

test('event filters carry content filters independently from event-only criteria', function () {
    $filters = new EventFilters(
        categories: [1, 2],
        tags: [3],
        locationId: 4,
        personId: 5,
        search: 'Yoga',
    );

    expect($filters->categories)->toBe([1, 2])
        ->and($filters->tags)->toBe([3])
        ->and($filters->locationId)->toBe(4)
        ->and($filters->personId)->toBe(5)
        ->and($filters->search)->toBe('Yoga');
});
