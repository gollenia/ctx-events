<?php

declare(strict_types=1);

use Contexis\Events\Communication\Domain\EmailDefinition;
use Contexis\Events\Communication\Domain\Enums\EmailTarget;
use Contexis\Events\Communication\Domain\Enums\EmailTrigger;
use Contexis\Events\Communication\Infrastructure\EmailDefinitionMapper;

test('maps database row into email definition', function () {
    $definition = EmailDefinitionMapper::map([
        'id' => 5,
        'event_id' => 42,
        'email_trigger' => 'booking_pending',
        'email_target' => 'customer',
        'enabled' => 1,
        'gateway' => 'offline',
        'subject' => 'Pending mail',
        'body' => 'Hello there',
        'reply_to' => 'reply@example.com',
    ]);

    expect($definition)->toBeInstanceOf(EmailDefinition::class);
    assert($definition instanceof EmailDefinition);

    expect($definition->id)->toBe('5');
    expect($definition->eventId?->toInt())->toBe(42);
    expect($definition->trigger)->toBe(EmailTrigger::BOOKING_PENDING);
    expect($definition->target)->toBe(EmailTarget::CUSTOMER);
    expect($definition->enabled)->toBeTrue();
    expect($definition->gateway)->toBe('offline');
    expect($definition->replyTo?->toString())->toBe('reply@example.com');
});

test('throws for invalid trigger', function () {
    expect(fn () => EmailDefinitionMapper::map([
        'id' => 5,
        'event_id' => 42,
        'email_trigger' => 'invalid',
        'email_target' => 'customer',
        'enabled' => 1,
        'body' => 'Hello there',
    ]))->toThrow(InvalidArgumentException::class);
});
