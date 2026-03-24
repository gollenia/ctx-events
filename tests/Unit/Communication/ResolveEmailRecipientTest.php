<?php

declare(strict_types=1);

use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Communication\Application\ResolveEmailRecipient;
use Contexis\Events\Communication\Domain\Enums\EmailTarget;
use Contexis\Events\Communication\Domain\ValueObjects\AdminEmailRecipientConfig;
use Contexis\Events\Person\Domain\Person;
use Contexis\Events\Person\Domain\PersonId;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Contexis\Events\Shared\Domain\ValueObjects\Status;
use Tests\Support\FakeBookingOptions;
use Tests\Support\FakeEventFactory;
use Tests\Support\FakeEventRepository;
use Tests\Support\FakePersonRepository;

test('resolves customer recipient from booking email', function () {
    $booking = testBooking(42);
    $resolver = new ResolveEmailRecipient(
        FakeEventRepository::empty(),
        new FakePersonRepository(),
        new FakeBookingOptions(),
    );

    $recipient = $resolver->execute(EmailTarget::CUSTOMER, $booking);

    expect($recipient?->toString())->toBe('customer@example.com');
});

test('resolves billing contact from registration data', function () {
    $booking = testBooking(42, [
        'billing_email' => 'billing@example.com',
    ]);
    $resolver = new ResolveEmailRecipient(
        FakeEventRepository::empty(),
        new FakePersonRepository(),
        new FakeBookingOptions(),
    );

    $recipient = $resolver->execute(EmailTarget::BILLING_CONTACT, $booking);

    expect($recipient?->toString())->toBe('billing@example.com');
});

test('resolves event contact from linked person email', function () {
    $event = FakeEventFactory::create(42);
    $personId = $event->personId ?? PersonId::from(99);
    $person = new Person(
        id: $personId,
        status: Status::Draft,
        givenName: 'Grace',
        familyName: 'Hopper',
        email: Email::tryFrom('speaker@example.com'),
    );

    $booking = testBooking($event->id->toInt());
    $resolver = new ResolveEmailRecipient(
        FakeEventRepository::one($event),
        new FakePersonRepository($person),
        new FakeBookingOptions(),
    );

    $recipient = $resolver->execute(EmailTarget::EVENT_CONTACT, $booking);

    expect($recipient?->toString())->toBe('speaker@example.com');
});

test('returns null when admin recipient source is not defined yet', function () {
    $booking = testBooking(42);
    $resolver = new ResolveEmailRecipient(
        FakeEventRepository::empty(),
        new FakePersonRepository(),
        new FakeBookingOptions(),
    );

    expect($resolver->execute(EmailTarget::ADMIN, $booking))->toBeNull();
});

test('resolves configured admin recipients and deduplicates them', function () {
    $event = FakeEventFactory::create(43);
    $personId = $event->personId ?? PersonId::from(100);
    $person = new Person(
        id: $personId,
        status: Status::Draft,
        givenName: 'Grace',
        familyName: 'Hopper',
        email: Email::tryFrom('speaker@example.com'),
    );

    $booking = testBooking($event->id->toInt());
    $resolver = new ResolveEmailRecipient(
        FakeEventRepository::one($event),
        new FakePersonRepository($person),
        new FakeBookingOptions(adminNotificationEmail: Email::tryFrom('booking-admin@example.com')),
    );

    $recipients = $resolver->executeMany(
        EmailTarget::ADMIN,
        $booking,
        $event,
        new AdminEmailRecipientConfig(
            sendToEventContact: true,
            sendToEventPerson: true,
            sendToBookingAdmin: true,
            sendToWpAdmin: false,
            customRecipients: ['custom@example.com', 'speaker@example.com'],
        ),
    );

    expect(array_map(static fn (Email $email): string => $email->toString(), $recipients))
        ->toEqualCanonicalizing([
            'speaker@example.com',
            'booking-admin@example.com',
            'custom@example.com',
        ]);
});

/**
 * @param array<string, string> $registration
 */
function testBooking(int $eventId, array $registration = []): Booking
{
    $registration = [
        'email' => 'customer@example.com',
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        ...$registration,
    ];

    $email = Email::tryFrom((string) $registration['email']);
    $resolvedEventId = \Contexis\Events\Event\Domain\ValueObjects\EventId::from($eventId);

    if ($email === null || $resolvedEventId === null) {
        throw new RuntimeException('Invalid booking fixture.');
    }

    return new Booking(
        reference: BookingReference::fromString('CTX-BOOK-001'),
        email: $email,
        name: PersonName::from((string) $registration['first_name'], (string) $registration['last_name']),
        priceSummary: PriceSummary::free(),
        bookingTime: new DateTimeImmutable('2026-03-16 10:00:00'),
        status: BookingStatus::PENDING,
        registration: new RegistrationData($registration),
        attendees: AttendeeCollection::empty(),
        gateway: 'offline',
        coupon: null,
        transactions: null,
        eventId: $resolvedEventId,
    );
}
