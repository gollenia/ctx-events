<?php

declare(strict_types=1);

use Contexis\Events\Event\Infrastructure\EventMeta;
use Contexis\Events\Event\Infrastructure\RecurringEventMeta;

test('recurring exceptions are stored as keyed series metadata', function () {
    $metadata = RecurringEventMeta::getRegisterArgs();
    $exceptions = $metadata[RecurringEventMeta::EXCEPTIONS];

    expect($exceptions['type'])->toBe('object')
        ->and($exceptions['default'])->toBe([])
        ->and($exceptions['show_in_rest']['schema']['additionalProperties']['required'])->toBe(['type']);
});

test('detached events retain their originating occurrence key', function () {
    $metadata = EventMeta::getRegisterArgs();

    expect($metadata)->toHaveKey(EventMeta::RECURRENCE_OCCURRENCE_KEY)
        ->and($metadata[EventMeta::RECURRENCE_OCCURRENCE_KEY]['type'])->toBe('string');
});
