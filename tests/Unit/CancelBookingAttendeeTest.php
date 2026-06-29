<?php
declare(strict_types=1);

use Contexis\Events\Booking\Application\DTOs\CancelBookingAttendeeRequest;
use Contexis\Events\Booking\Application\UseCases\CancelBookingAttendee;
use Contexis\Events\Booking\Domain\Attendee;
use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\Enums\AttendeeStatus;
use Contexis\Events\Booking\Domain\Enums\BookingLogEvent;
use Contexis\Events\Booking\Domain\Services\CalculateBookingPrice;
use Contexis\Events\Booking\Domain\ValueObjects\AttendeeId;
use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Communication\Domain\Enums\EmailTrigger;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Tests\Support\FakeBookingRepository;
use Tests\Support\FakeBookingEmailTrigger;
use Tests\Support\FakeCurrentActorProvider;
use Tests\Support\FakeEventFactory;
use Tests\Support\FakeEventRepository;

test('cancel booking attendee overwrites attendee price and updates booking summary', function () {
    $event = FakeEventFactory::create(101);
    $ticket = $event->tickets?->first() ?? throw new RuntimeException('Missing ticket');
    $repository = FakeBookingRepository::empty();

    $bookingId = $repository->save(new \Contexis\Events\Booking\Domain\Booking(
        reference: BookingReference::fromString('BOOK-101'),
        email: Email::tryFrom('max@example.com') ?? throw new RuntimeException('Invalid email'),
        name: new PersonName('Max', 'Muster'),
        priceSummary: PriceSummary::fromValues(
            bookingPrice: Price::from(3000, $ticket->price->currency),
            donationAmount: Price::from(0, $ticket->price->currency),
            discountAmount: Price::from(0, $ticket->price->currency),
        ),
        bookingTime: new DateTimeImmutable('2026-03-10 10:00:00'),
        status: BookingStatus::APPROVED,
        registration: new RegistrationData([
            'email' => 'max@example.com',
            'first_name' => 'Max',
            'last_name' => 'Muster',
        ]),
        attendees: AttendeeCollection::from(
            new Attendee(
                ticketId: $ticket->id,
                ticketPrice: $ticket->price,
                name: new PersonName('Max', 'Muster'),
                metadata: ['first_name' => 'Max', 'last_name' => 'Muster'],
                status: AttendeeStatus::ACTIVE,
                id: AttendeeId::from(11),
            ),
            new Attendee(
                ticketId: $ticket->id,
                ticketPrice: $ticket->price,
                name: new PersonName('Erika', 'Muster'),
                metadata: ['first_name' => 'Erika', 'last_name' => 'Muster'],
                status: AttendeeStatus::ACTIVE,
                id: AttendeeId::from(12),
            ),
        ),
        gateway: 'offline',
        coupon: null,
        transactions: null,
        eventId: $event->id,
    ));

    $booking = $repository->find($bookingId) ?? throw new RuntimeException('Booking not found');

    $clock = Mockery::mock(Clock::class);
    $clock->allows('now')->andReturn(new DateTimeImmutable('2026-03-10 11:00:00'));
    $bookingEmailTrigger = new FakeBookingEmailTrigger();

    $useCase = new CancelBookingAttendee(
        bookingRepository: $repository,
        eventRepository: FakeEventRepository::one($event),
        calculateBookingPrice: new CalculateBookingPrice(),
        clock: $clock,
        currentActorProvider: new FakeCurrentActorProvider(),
        bookingEmailTrigger: $bookingEmailTrigger,
    );

    $result = $useCase->execute(new CancelBookingAttendeeRequest(
        reference: $booking->reference->toString(),
        attendeeId: 11,
        cancellationAmountCents: 500,
    ));

    $updated = $repository->find($bookingId) ?? throw new RuntimeException('Updated booking missing');
    $cancelled = $updated->attendees->getById(AttendeeId::from(11));
    $active = $updated->attendees->getById(AttendeeId::from(12));

    expect($cancelled)->not->toBeNull();
    expect($cancelled?->status)->toBe(AttendeeStatus::CANCELLED);
    expect($cancelled?->ticketPrice->toInt())->toBe(500);
    expect($active?->status)->toBe(AttendeeStatus::ACTIVE);
    expect($updated->countAttendees())->toBe(1);
    expect($updated->priceSummary->bookingPrice->toInt())->toBe(2000);
    expect($updated->priceSummary->finalPrice->toInt())->toBe(2000);
    expect($updated->logEntries?->toArray()[0]->eventType)->toBe(BookingLogEvent::AttendeeCancelled);
    expect($result->deliveries)->toBe([]);
    $lastCall = $bookingEmailTrigger->lastCall();
    expect($lastCall)->not->toBeNull();
    expect($lastCall['trigger'])->toBe(EmailTrigger::TICKET_CANCELLED);
});

test('last active attendee cannot be cancelled through attendee cancellation', function () {
    $event = FakeEventFactory::create(102);
    $ticket = $event->tickets?->first() ?? throw new RuntimeException('Missing ticket');
    $repository = FakeBookingRepository::empty();

    $bookingId = $repository->save(new \Contexis\Events\Booking\Domain\Booking(
        reference: BookingReference::fromString('BOOK-102'),
        email: Email::tryFrom('max@example.com') ?? throw new RuntimeException('Invalid email'),
        name: new PersonName('Max', 'Muster'),
        priceSummary: PriceSummary::fromValues(
            bookingPrice: Price::from($ticket->price->toInt(), $ticket->price->currency),
            donationAmount: Price::from(0, $ticket->price->currency),
            discountAmount: Price::from(0, $ticket->price->currency),
        ),
        bookingTime: new DateTimeImmutable('2026-03-10 10:00:00'),
        status: BookingStatus::APPROVED,
        registration: new RegistrationData([
            'email' => 'max@example.com',
            'first_name' => 'Max',
            'last_name' => 'Muster',
        ]),
        attendees: AttendeeCollection::from(
            new Attendee(
                ticketId: $ticket->id,
                ticketPrice: $ticket->price,
                name: new PersonName('Max', 'Muster'),
                metadata: ['first_name' => 'Max', 'last_name' => 'Muster'],
                status: AttendeeStatus::ACTIVE,
                id: AttendeeId::from(21),
            ),
        ),
        gateway: 'offline',
        coupon: null,
        transactions: null,
        eventId: $event->id,
    ));

    $booking = $repository->find($bookingId) ?? throw new RuntimeException('Booking not found');

    $clock = Mockery::mock(Clock::class);
    $clock->allows('now')->andReturn(new DateTimeImmutable('2026-03-10 11:00:00'));

    $useCase = new CancelBookingAttendee(
        bookingRepository: $repository,
        eventRepository: FakeEventRepository::one($event),
        calculateBookingPrice: new CalculateBookingPrice(),
        clock: $clock,
        currentActorProvider: new FakeCurrentActorProvider(),
        bookingEmailTrigger: new FakeBookingEmailTrigger(),
    );

    expect(fn () => $useCase->execute(new CancelBookingAttendeeRequest(
        reference: $booking->reference->toString(),
        attendeeId: 21,
        cancellationAmountCents: 500,
    )))->toThrow(\DomainException::class, 'Cancel the booking instead.');
});

test('cancel booking attendee skips email trigger when send mail is disabled', function () {
    $event = FakeEventFactory::create(103);
    $ticket = $event->tickets?->first() ?? throw new RuntimeException('Missing ticket');
    $repository = FakeBookingRepository::empty();

    $bookingId = $repository->save(new \Contexis\Events\Booking\Domain\Booking(
        reference: BookingReference::fromString('BOOK-103'),
        email: Email::tryFrom('max@example.com') ?? throw new RuntimeException('Invalid email'),
        name: new PersonName('Max', 'Muster'),
        priceSummary: PriceSummary::fromValues(
            bookingPrice: Price::from(3000, $ticket->price->currency),
            donationAmount: Price::from(0, $ticket->price->currency),
            discountAmount: Price::from(0, $ticket->price->currency),
        ),
        bookingTime: new DateTimeImmutable('2026-03-10 10:00:00'),
        status: BookingStatus::APPROVED,
        registration: new RegistrationData([
            'email' => 'max@example.com',
            'first_name' => 'Max',
            'last_name' => 'Muster',
        ]),
        attendees: AttendeeCollection::from(
            new Attendee(
                ticketId: $ticket->id,
                ticketPrice: $ticket->price,
                name: new PersonName('Max', 'Muster'),
                metadata: ['first_name' => 'Max', 'last_name' => 'Muster'],
                status: AttendeeStatus::ACTIVE,
                id: AttendeeId::from(31),
            ),
            new Attendee(
                ticketId: $ticket->id,
                ticketPrice: $ticket->price,
                name: new PersonName('Erika', 'Muster'),
                metadata: ['first_name' => 'Erika', 'last_name' => 'Muster'],
                status: AttendeeStatus::ACTIVE,
                id: AttendeeId::from(32),
            ),
        ),
        gateway: 'offline',
        coupon: null,
        transactions: null,
        eventId: $event->id,
    ));

    $booking = $repository->find($bookingId) ?? throw new RuntimeException('Booking not found');
    $clock = Mockery::mock(Clock::class);
    $clock->allows('now')->andReturn(new DateTimeImmutable('2026-03-10 11:00:00'));
    $bookingEmailTrigger = new FakeBookingEmailTrigger();

    $useCase = new CancelBookingAttendee(
        bookingRepository: $repository,
        eventRepository: FakeEventRepository::one($event),
        calculateBookingPrice: new CalculateBookingPrice(),
        clock: $clock,
        currentActorProvider: new FakeCurrentActorProvider(),
        bookingEmailTrigger: $bookingEmailTrigger,
    );

    $result = $useCase->execute(new CancelBookingAttendeeRequest(
        reference: $booking->reference->toString(),
        attendeeId: 31,
        cancellationAmountCents: 500,
        sendMail: false,
    ));

    expect($result->deliveries)->toBe([]);
    expect($bookingEmailTrigger->lastCall())->toBeNull();
});
