<?php

declare(strict_types=1);

use Contexis\Events\Communication\Application\ResolveEmailDefinition;
use Contexis\Events\Communication\Domain\EmailDefinition;
use Contexis\Events\Communication\Domain\EmailDefinitionCollection;
use Contexis\Events\Communication\Domain\Enums\EmailTarget;
use Contexis\Events\Communication\Domain\Enums\EmailTrigger;
use Contexis\Events\Communication\Domain\ValueObjects\EmailContext;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Tests\Support\FakeEmailDefinitionRepository;

test('resolves applicable email definition through repository and domain fallback', function () {
    $eventId = EventId::from(42);

    $repository = new FakeEmailDefinitionRepository(EmailDefinitionCollection::from(
        new EmailDefinition(
            id: 'global',
            trigger: EmailTrigger::BOOKING_PENDING,
            target: EmailTarget::CUSTOMER,
            enabled: true,
            eventId: null,
            gateway: null,
            subject: 'Global subject',
            body: 'Global body',
        ),
        new EmailDefinition(
            id: 'event',
            trigger: EmailTrigger::BOOKING_PENDING,
            target: EmailTarget::CUSTOMER,
            enabled: true,
            eventId: $eventId,
            gateway: null,
            subject: 'Event subject',
            body: 'Event body',
        ),
    ));

    $useCase = new ResolveEmailDefinition($repository);

    $resolved = $useCase->execute(new EmailContext(
        trigger: EmailTrigger::BOOKING_PENDING,
        eventId: $eventId,
        target: EmailTarget::CUSTOMER,
    ));

    expect($resolved)->not->toBeNull();
    expect($resolved?->id)->toBe('event');
});

test('returns null when no applicable email definition exists', function () {
    $eventId = EventId::from(42);
    $repository = new FakeEmailDefinitionRepository(EmailDefinitionCollection::empty());
    $useCase = new ResolveEmailDefinition($repository);

    $resolved = $useCase->execute(new EmailContext(
        trigger: EmailTrigger::BOOKING_PENDING,
        eventId: $eventId,
        target: EmailTarget::CUSTOMER,
    ));

    expect($resolved)->toBeNull();
});
