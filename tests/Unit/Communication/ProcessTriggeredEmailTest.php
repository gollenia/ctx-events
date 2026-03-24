<?php

declare(strict_types=1);

use Contexis\Events\Communication\Application\ResolveEmailRecipient;
use Contexis\Events\Communication\Application\Services\LoadBookingEmailContext;
use Contexis\Events\Communication\Application\Services\SendBookingEmails;
use Contexis\Events\Communication\Infrastructure\EmailTemplateTokenReplacer;
use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Event\Application\Service\IcalEventCalendarExporter;
use Contexis\Events\Communication\Infrastructure\TiptapDocumentRenderer;
use Contexis\Events\Communication\Infrastructure\TiptapEmailBodyRenderer;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Communication\Application\DTOs\BookingEmailDeliveryResult;
use Contexis\Events\Communication\Domain\Enums\EmailTrigger;
use Contexis\Events\Communication\Domain\Enums\EmailTemplateKey;
use Contexis\Events\Communication\Infrastructure\DefaultEmailTemplatePresetProvider;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Tests\Support\FakeAttendeeRepository;
use Tests\Support\FakeBookingRepository;
use Tests\Support\FakeBookingOptions;
use Tests\Support\FakeEmailSender;
use Tests\Support\FakeEventMailTemplateOverrideStore;
use Tests\Support\FakeEmailTemplateOverrideStore;
use Tests\Support\FakeEventFactory;
use Tests\Support\FakeEventRepository;
use Tests\Support\FakePersonRepository;
use Tests\Support\FakeTransactionRepository;

function makeTriggeredEmailProcessorBooking(EventId $eventId): Booking
{
    return new Booking(
        reference: BookingReference::fromString('BOOK-MAIL-1001'),
        email: new Email('booking@example.test'),
        name: PersonName::from('Max', 'Mustermann'),
        priceSummary: PriceSummary::fromValues(
            bookingPrice: Price::from(5000, Currency::fromCode('EUR')),
            donationAmount: Price::from(0, Currency::fromCode('EUR')),
            discountAmount: Price::from(0, Currency::fromCode('EUR'))
        ),
        bookingTime: new DateTimeImmutable('2026-03-10 10:00:00'),
        status: BookingStatus::PENDING,
        registration: new RegistrationData([
            'email' => 'booking@example.test',
            'first_name' => 'Max',
            'last_name' => 'Mustermann',
        ]),
        attendees: AttendeeCollection::empty(),
        gateway: 'offline',
        coupon: null,
        transactions: null,
        eventId: $eventId,
    );
}

function makeTriggeredEmailProcessor(
    FakeBookingRepository $bookingRepository,
    FakeEventRepository $eventRepository,
    FakeAttendeeRepository $attendeeRepository,
    FakeTransactionRepository $transactionRepository,
    FakeEmailSender $emailSender,
    ?FakeEmailTemplateOverrideStore $overrideStore = null,
    ?FakeBookingOptions $bookingOptions = null,
    ?FakeEventMailTemplateOverrideStore $eventOverrideStore = null,
): SendBookingEmails {
    return new SendBookingEmails(
        new LoadBookingEmailContext(
            bookingRepository: $bookingRepository,
            eventRepository: $eventRepository,
            attendeeRepository: $attendeeRepository,
            transactionRepository: $transactionRepository,
        ),
        new DefaultEmailTemplatePresetProvider(),
        $overrideStore ?? new FakeEmailTemplateOverrideStore(),
        $eventOverrideStore ?? new FakeEventMailTemplateOverrideStore(),
        new IcalEventCalendarExporter(),
        $bookingOptions ?? new FakeBookingOptions(),
        new TiptapEmailBodyRenderer(
            new TiptapDocumentRenderer(),
            new EmailTemplateTokenReplacer(),
        ),
        new EmailTemplateTokenReplacer(),
        new ResolveEmailRecipient($eventRepository, new FakePersonRepository(), $bookingOptions ?? new FakeBookingOptions()),
        $emailSender
    );
}

test('sends the customer mail and skips unresolved admin mail for pending manual bookings', function () {
    $event = FakeEventFactory::create(210);
    $bookingRepository = FakeBookingRepository::empty();
    $bookingId = $bookingRepository->save(makeTriggeredEmailProcessorBooking($event->id));
    $attendeeRepository = FakeAttendeeRepository::empty();
    $transactionRepository = FakeTransactionRepository::empty();
    $emailSender = new FakeEmailSender();
    $overrideStore = new FakeEmailTemplateOverrideStore([
        EmailTemplateKey::ADMIN_BOOKING_PENDING_MANUAL->value => [
            'recipientConfig' => [
                'sendToEventContact' => false,
                'sendToEventPerson' => false,
                'sendToBookingAdmin' => false,
                'sendToWpAdmin' => false,
                'customRecipients' => [],
            ],
        ],
    ]);

    $processor = makeTriggeredEmailProcessor(
        $bookingRepository,
        FakeEventRepository::one($event),
        $attendeeRepository,
        $transactionRepository,
        $emailSender,
        $overrideStore,
    );

    $result = $processor->trigger(EmailTrigger::BOOKING_PENDING_MANUAL, $bookingId);

    expect($bookingRepository->lastFindArg)->toEqual($bookingId);
    expect($attendeeRepository->lastFindArg)->toEqual($bookingId);
    expect($transactionRepository->lastFindArg)->toEqual($bookingId);
    expect($emailSender->lastEmail?->to->toString())->toBe('booking@example.test');
    expect($emailSender->lastEmail?->subject)->toBe('We received your booking');
    expect($result->deliveries)->toHaveCount(2);
    expect(array_map(
        static fn ($delivery): string => $delivery->status,
        $result->deliveries,
    ))->toContain('sent', 'skipped');
});

test('records skipped admin delivery when no admin recipient can be resolved', function () {
    $event = FakeEventFactory::create(211);
    $bookingRepository = FakeBookingRepository::empty();
    $bookingId = $bookingRepository->save(makeTriggeredEmailProcessorBooking($event->id));
    $attendeeRepository = FakeAttendeeRepository::empty();
    $transactionRepository = FakeTransactionRepository::empty();
    $emailSender = new FakeEmailSender();

    $processor = makeTriggeredEmailProcessor(
        $bookingRepository,
        FakeEventRepository::one($event),
        $attendeeRepository,
        $transactionRepository,
        $emailSender,
    );

    $result = $processor->trigger(EmailTrigger::BOOKING_PENDING_MANUAL, $bookingId);

    expect($emailSender->lastEmail?->to->toString())->toBe('booking@example.test');
    expect($result->deliveries)->toHaveCount(2);
    expect(array_values(array_filter(
        $result->deliveries,
        static fn (BookingEmailDeliveryResult $delivery): bool =>
            $delivery->status === BookingEmailDeliveryResult::STATUS_SKIPPED
            && $delivery->reason === 'recipient_not_resolved',
    )))->toHaveCount(1);
});

test('sends admin emails to configured recipients', function () {
    $event = FakeEventFactory::create(213);
    $bookingRepository = FakeBookingRepository::empty();
    $bookingId = $bookingRepository->save(makeTriggeredEmailProcessorBooking($event->id));
    $attendeeRepository = FakeAttendeeRepository::empty();
    $transactionRepository = FakeTransactionRepository::empty();
    $emailSender = new FakeEmailSender();
    $overrideStore = new FakeEmailTemplateOverrideStore([
        EmailTemplateKey::ADMIN_BOOKING_PENDING_MANUAL->value => [
            'recipientConfig' => [
                'sendToBookingAdmin' => true,
                'sendToWpAdmin' => false,
                'customRecipients' => ['ops@example.com'],
            ],
        ],
    ]);
    $bookingOptions = new FakeBookingOptions(adminNotificationEmail: Email::tryFrom('booking-admin@example.com'));

    $processor = makeTriggeredEmailProcessor(
        $bookingRepository,
        FakeEventRepository::one($event),
        $attendeeRepository,
        $transactionRepository,
        $emailSender,
        $overrideStore,
        $bookingOptions,
    );

    $result = $processor->trigger(EmailTrigger::BOOKING_PENDING_MANUAL, $bookingId);

    expect($result->deliveries)->toHaveCount(3);
    expect(array_map(
        static fn (BookingEmailDeliveryResult $delivery): string => $delivery->recipient->toString(),
        $result->deliveries	
    ))->toEqualCanonicalizing([
        'booking@example.test',
        'booking-admin@example.com',
        'ops@example.com',
    ]);
});

test('logs failed sends', function () {
    $event = FakeEventFactory::create(212);
    $bookingRepository = FakeBookingRepository::empty();
    $bookingId = $bookingRepository->save(makeTriggeredEmailProcessorBooking($event->id));
    $attendeeRepository = FakeAttendeeRepository::empty();
    $transactionRepository = FakeTransactionRepository::empty();
    $emailSender = new FakeEmailSender();
    $emailSender->shouldSucceed = false;

    $processor = makeTriggeredEmailProcessor(
        $bookingRepository,
        FakeEventRepository::one($event),
        $attendeeRepository,
        $transactionRepository,
        $emailSender
    );

    $result = $processor->trigger(EmailTrigger::BOOKING_PENDING_MANUAL, $bookingId);

    expect($result->hasFailures())->toBeTrue();
		
});

test('event mail overrides take precedence over global template overrides during sending', function () {
    $event = FakeEventFactory::create(214);
    $bookingRepository = FakeBookingRepository::empty();
    $bookingId = $bookingRepository->save(makeTriggeredEmailProcessorBooking($event->id));
    $attendeeRepository = FakeAttendeeRepository::empty();
    $transactionRepository = FakeTransactionRepository::empty();
    $emailSender = new FakeEmailSender();
    $overrideStore = new FakeEmailTemplateOverrideStore([
        EmailTemplateKey::BOOKING_PENDING_MANUAL->value => [
            'subject' => 'Global override subject',
            'body' => 'Global override body',
            'replyTo' => 'global@example.test',
        ],
    ]);
    $eventOverrideStore = new FakeEventMailTemplateOverrideStore([
        $event->id->toInt() => [
            EmailTemplateKey::BOOKING_PENDING_MANUAL->value => [
                'key' => EmailTemplateKey::BOOKING_PENDING_MANUAL->value,
                'enabled' => true,
                'subject' => 'Event-specific subject',
                'body' => 'Event-specific body',
                'replyTo' => 'event@example.test',
            ],
        ],
    ]);

    $processor = makeTriggeredEmailProcessor(
        $bookingRepository,
        FakeEventRepository::one($event),
        $attendeeRepository,
        $transactionRepository,
        $emailSender,
        overrideStore: $overrideStore,
        eventOverrideStore: $eventOverrideStore,
    );

    $result = $processor->trigger(EmailTrigger::BOOKING_PENDING_MANUAL, $bookingId);

    expect($result->deliveries)->toHaveCount(2);
    expect($emailSender->lastEmail?->subject)->toBe('Event-specific subject');
    expect($emailSender->lastEmail?->body)->toBe('Event-specific body');
    expect($emailSender->lastEmail?->replyTo?->toString())->toBe('event@example.test');
});

test('replaces template tokens in the subject during sending', function () {
    $event = FakeEventFactory::create(217);
    $bookingRepository = FakeBookingRepository::empty();
    $bookingId = $bookingRepository->save(makeTriggeredEmailProcessorBooking($event->id));
    $attendeeRepository = FakeAttendeeRepository::empty();
    $transactionRepository = FakeTransactionRepository::empty();
    $emailSender = new FakeEmailSender();
    $overrideStore = new FakeEmailTemplateOverrideStore([
        EmailTemplateKey::BOOKING_PENDING_MANUAL->value => [
            'subject' => 'Booking {{booking.reference}} for {{event.name}}',
        ],
    ]);

    $processor = makeTriggeredEmailProcessor(
        $bookingRepository,
        FakeEventRepository::one($event),
        $attendeeRepository,
        $transactionRepository,
        $emailSender,
        overrideStore: $overrideStore,
    );

    $processor->trigger(EmailTrigger::BOOKING_PENDING_MANUAL, $bookingId);

    expect($emailSender->lastEmail?->subject)->toBe(
        'Booking BOOK-MAIL-1001 for ' . $event->name,
    );
});

test('attaches an ical file to booking emails when the setting is enabled', function () {
    $event = FakeEventFactory::create(215);
    $bookingRepository = FakeBookingRepository::empty();
    $bookingId = $bookingRepository->save(makeTriggeredEmailProcessorBooking($event->id));
    $attendeeRepository = FakeAttendeeRepository::empty();
    $transactionRepository = FakeTransactionRepository::empty();
    $emailSender = new FakeEmailSender();

    $processor = makeTriggeredEmailProcessor(
        $bookingRepository,
        FakeEventRepository::one($event),
        $attendeeRepository,
        $transactionRepository,
        $emailSender,
        bookingOptions: new FakeBookingOptions(attachIcalToBookingEmail: true),
    );

    $result = $processor->trigger(EmailTrigger::BOOKING_PENDING_MANUAL, $bookingId);

    expect($result->deliveries)->toHaveCount(2);
    expect($emailSender->lastEmail)->not->toBeNull();
    expect($emailSender->lastEmail?->attachments)->toHaveCount(1);
    expect($emailSender->lastEmail?->attachments[0]->filename)->toEndWith('.ics');
    expect($emailSender->lastEmail?->attachments[0]->mimeType)->toBe('text/calendar; charset=UTF-8');
    expect($emailSender->lastEmail?->attachments[0]->content)->toContain('BEGIN:VCALENDAR', 'BEGIN:VEVENT', 'SUMMARY:');
});

test('skips matching templates when booking context cannot be loaded', function () {
    $bookingRepository = FakeBookingRepository::empty();
    $attendeeRepository = FakeAttendeeRepository::empty();
    $transactionRepository = FakeTransactionRepository::empty();
    $emailSender = new FakeEmailSender();

    $processor = makeTriggeredEmailProcessor(
        $bookingRepository,
        FakeEventRepository::empty(),
        $attendeeRepository,
        $transactionRepository,
        $emailSender,
    );

    $result = $processor->trigger(EmailTrigger::BOOKING_PENDING_MANUAL, \Contexis\Events\Booking\Domain\ValueObjects\BookingId::from(999));

    expect($bookingRepository->lastFindArg?->toInt())->toBe(999);
    expect($attendeeRepository->lastFindArg)->toBeNull();
    expect($transactionRepository->lastFindArg)->toBeNull();
    expect($result->deliveries)->toHaveCount(2);
});
