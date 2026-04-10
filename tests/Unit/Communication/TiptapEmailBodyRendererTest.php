<?php

declare(strict_types=1);

use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Communication\Application\DTOs\TriggeredEmailContext;
use Contexis\Events\Communication\Infrastructure\EmailTemplateTokenReplacer;
use Contexis\Events\Communication\Infrastructure\TiptapDocumentRenderer;
use Contexis\Events\Communication\Infrastructure\TiptapEmailBodyRenderer;
use Contexis\Events\Payment\Domain\TransactionCollection;
use Tests\Support\FakeEventFactory;

test('renders legacy mail bodies as plain text with replaced tokens', function () {
    $event = FakeEventFactory::create(701);
    $booking = makeTriggeredEmailProcessorBooking($event->id);
    $renderer = new TiptapEmailBodyRenderer(
        new TiptapDocumentRenderer(),
        new EmailTemplateTokenReplacer(),
    );

    $rendered = $renderer->render(
        'Hello {{booking.first_name}}',
        new TriggeredEmailContext(
            booking: $booking,
            event: $event,
            attendees: AttendeeCollection::empty(),
            transactions: TransactionCollection::empty(),
            cancellationReason: null,
        ),
    );

    expect($rendered->isHtml)->toBeFalse();
    expect($rendered->content)->toBe('Hello Max');
});

test('renders tiptap mail bodies as html with replaced tokens', function () {
    $event = FakeEventFactory::create(702);
    $booking = makeTriggeredEmailProcessorBooking($event->id);
    $renderer = new TiptapEmailBodyRenderer(
        new TiptapDocumentRenderer(),
        new EmailTemplateTokenReplacer(),
    );

    $rendered = $renderer->render(
        json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'Hello '],
                        ['type' => 'text', 'text' => '{{booking.first_name}}'],
                        ['type' => 'text', 'text' => ' at '],
                        ['type' => 'text', 'text' => '{{event.name}}'],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR),
        new TriggeredEmailContext(
            booking: $booking,
            event: $event,
            attendees: AttendeeCollection::empty(),
            transactions: TransactionCollection::empty(),
            cancellationReason: null,
        ),
    );

    expect($rendered->isHtml)->toBeTrue();
    expect($rendered->content)->toContain('<p>', 'Hello Max', $event->name);
});

test('renders cancellation reason token when present', function () {
    $event = FakeEventFactory::create(703);
    $booking = makeTriggeredEmailProcessorBooking($event->id);
    $renderer = new TiptapEmailBodyRenderer(
        new TiptapDocumentRenderer(),
        new EmailTemplateTokenReplacer(),
    );

    $rendered = $renderer->render(
        'Reason: {{booking.cancellation_reason}}',
        new TriggeredEmailContext(
            booking: $booking,
            event: $event,
            attendees: AttendeeCollection::empty(),
            transactions: TransactionCollection::empty(),
            cancellationReason: 'Speaker is ill',
        ),
    );

    expect($rendered->content)->toBe('Reason: Speaker is ill');
});
