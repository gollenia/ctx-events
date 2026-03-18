<?php

declare(strict_types=1);

use Contexis\Events\Communication\Domain\EmailDefinition;
use Contexis\Events\Communication\Domain\EmailDefinitionCollection;
use Contexis\Events\Communication\Domain\Enums\EmailTarget;
use Contexis\Events\Communication\Domain\Enums\EmailTrigger;
use Contexis\Events\Communication\Domain\ValueObjects\EmailContext;
use Contexis\Events\Event\Domain\ValueObjects\EventId;

test('prefers event-specific gateway mail over generic fallbacks', function () {
    $eventId = EventId::from(42);

    $definitions = EmailDefinitionCollection::from(
        new EmailDefinition(
            id: 'global-generic',
            trigger: EmailTrigger::BOOKING_PENDING,
            target: EmailTarget::CUSTOMER,
            enabled: true,
            eventId: null,
            gateway: null,
            subject: 'Global generic',
            body: 'Global generic body',
        ),
        new EmailDefinition(
            id: 'event-generic',
            trigger: EmailTrigger::BOOKING_PENDING,
            target: EmailTarget::CUSTOMER,
            enabled: true,
            eventId: $eventId,
            gateway: null,
            subject: 'Event generic',
            body: 'Event generic body',
        ),
        new EmailDefinition(
            id: 'event-offline',
            trigger: EmailTrigger::BOOKING_PENDING,
            target: EmailTarget::CUSTOMER,
            enabled: true,
            eventId: $eventId,
            gateway: 'offline',
            subject: 'Event offline',
            body: 'Event offline body',
        ),
    );

    $resolved = $definitions->resolve(new EmailContext(
        trigger: EmailTrigger::BOOKING_PENDING,
        eventId: $eventId,
        target: EmailTarget::CUSTOMER,
        gateway: 'offline',
    ));

    expect($resolved)->not->toBeNull()
        ->and($resolved->id)->toBe('event-offline');
});

test('falls back to event-specific generic mail before global mail', function () {
    $eventId = EventId::from(42);

    $definitions = EmailDefinitionCollection::from(
        new EmailDefinition(
            id: 'global-generic',
            trigger: EmailTrigger::BOOKING_PENDING,
            target: EmailTarget::CUSTOMER,
            enabled: true,
            eventId: null,
            gateway: null,
            subject: 'Global generic',
            body: 'Global generic body',
        ),
        new EmailDefinition(
            id: 'event-generic',
            trigger: EmailTrigger::BOOKING_PENDING,
            target: EmailTarget::CUSTOMER,
            enabled: true,
            eventId: $eventId,
            gateway: null,
            subject: 'Event generic',
            body: 'Event generic body',
        ),
    );

    $resolved = $definitions->resolve(new EmailContext(
        trigger: EmailTrigger::BOOKING_PENDING,
        eventId: $eventId,
        target: EmailTarget::CUSTOMER,
        gateway: 'mollie',
    ));

    expect($resolved)->not->toBeNull()
        ->and($resolved->id)->toBe('event-generic');
});

test('falls back to global mail when event has no own template', function () {
    $eventId = EventId::from(42);

    $definitions = EmailDefinitionCollection::from(
        new EmailDefinition(
            id: 'global-generic',
            trigger: EmailTrigger::BOOKING_PENDING,
            target: EmailTarget::CUSTOMER,
            enabled: true,
            eventId: null,
            gateway: null,
            subject: 'Global generic',
            body: 'Global generic body',
        ),
    );

    $resolved = $definitions->resolve(new EmailContext(
        trigger: EmailTrigger::BOOKING_PENDING,
        eventId: $eventId,
        target: EmailTarget::CUSTOMER,
    ));

    expect($resolved)->not->toBeNull()
        ->and($resolved->id)->toBe('global-generic');
});
