<?php
declare(strict_types=1);

use Contexis\Events\Booking\Application\DTOs\CreateBookingRequest;
use Contexis\Events\Booking\Application\Services\AttendeeFactory;
use Contexis\Events\Booking\Application\Services\BookingTokenValidator;
use Contexis\Events\Booking\Domain\Services\CalculateBookingPrice;
use Contexis\Events\Booking\Application\UseCases\CreateBooking;
use Contexis\Events\Booking\Domain\AttendeeRepository;
use Contexis\Events\Booking\Domain\ValueObjects\BookingTokenRecord;
use Contexis\Events\Event\Application\Service\CheckTicketAvailibility;
use Contexis\Events\Event\Infrastructure\EventMeta;
use Contexis\Events\Payment\Domain\CouponRepository;
use Contexis\Events\Payment\Domain\TransactionRepository;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\Contracts\SessionHashResolver;
use Tests\Support\FakeBookingRepository;
use Tests\Support\FakeCurrentActorProvider;
use Tests\Support\FakeEventFactory;
use Tests\Support\FakeEventRepository;
use Tests\Support\FakeFormRepository;
use Tests\Support\FakeGatewayRepository;
use Tests\Support\FakeReferenceGenerator;
use Tests\Support\FakeTokenStore;

// ---------------------------------------------------------------------------
// Helper: builds a fully-wired CreateBooking use-case
// ---------------------------------------------------------------------------

function makeCreateBookingUseCase(
    FakeEventRepository $eventRepository,
    FakeBookingRepository $bookingRepository,
    FakeTokenStore $tokenStore,
    string $sessionHash = 'test-session-hash',
    ?FakeGatewayRepository $gatewayRepository = null,
): CreateBooking {
    $sessionHashResolver = Mockery::mock(SessionHashResolver::class);
    $sessionHashResolver->allows('resolve')->andReturn($sessionHash);

    $attendeeRepository = Mockery::mock(AttendeeRepository::class);
    $attendeeRepository->allows('saveAll');
    $attendeeRepository->allows('deleteByBookingId');

    $transactionRepository = Mockery::mock(TransactionRepository::class);
    $transactionRepository->allows('save');
    $transactionRepository->allows('deleteByBookingId');

    $couponRepository = Mockery::mock(CouponRepository::class);
    $couponRepository->allows('findByCode')->andReturn(null);

    $clock = Mockery::mock(Clock::class);
    $clock->allows('now')->andReturn(new DateTimeImmutable('2026-03-10 10:00:00'));

    return new CreateBooking(
        bookingRepository: $bookingRepository,
        attendeeRepository: $attendeeRepository,
        eventRepository: $eventRepository,
        gatewayRepository: $gatewayRepository ?? FakeGatewayRepository::withActiveGateway(),
        transactionRepository: $transactionRepository,
        referenceGenerator: new FakeReferenceGenerator(),
        attendeeFactory: new AttendeeFactory(),
        clock: $clock,
        currentActorProvider: new FakeCurrentActorProvider(),
        checkTicketAvailibility: new CheckTicketAvailibility(),
        calculateBookingPrice: new CalculateBookingPrice(),
        couponRepository: $couponRepository,
        tokenValidator: new BookingTokenValidator($tokenStore, $sessionHashResolver),
    );
}

// ---------------------------------------------------------------------------
// Helper: valid token for an event
// ---------------------------------------------------------------------------

function tokenFor(int $eventId, string $tokenId = 'valid-token', string $sessionHash = 'test-session-hash'): BookingTokenRecord
{
    return new BookingTokenRecord(
        tokenId: $tokenId,
        eventId: $eventId,
        sessionHash: $sessionHash,
        expiresAt: new DateTimeImmutable('+1 hour'),
    );
}

// ---------------------------------------------------------------------------
// Helper: minimal attendee payload for a ticket
// ---------------------------------------------------------------------------

function attendeePayload(string $ticketId): array
{
    return [[
        'ticket_id' => $ticketId,
        'metadata' => ['first_name' => 'Max', 'last_name' => 'Muster'],
    ]];
}

// ---------------------------------------------------------------------------
// Helper: minimal registration payload
// ---------------------------------------------------------------------------

function registrationPayload(): array
{
    return [
        'email' => 'max@example.com',
        'first_name' => 'Max',
        'last_name' => 'Muster',
    ];
}

// ===========================================================================
// Token validation
// ===========================================================================

test('throws when token is null', function () {
    $event = FakeEventFactory::create(1);
    $useCase = makeCreateBookingUseCase(
        FakeEventRepository::one($event),
        FakeBookingRepository::empty(),
        FakeTokenStore::empty(),
    );

    $request = new CreateBookingRequest(
        eventId: $event->id,
        registration: registrationPayload(),
        attendees: attendeePayload('ticket-abc-0001'),
        gateway: 'manual',
        token: null,
    );

    expect(fn () => $useCase->execute($request))
        ->toThrow(\DomainException::class, 'Booking token is required.');
});

test('throws when token is not found in store', function () {
    $event = FakeEventFactory::create(2);
    $useCase = makeCreateBookingUseCase(
        FakeEventRepository::one($event),
        FakeBookingRepository::empty(),
        FakeTokenStore::empty(), // store is empty – token unknown
    );

    $request = new CreateBookingRequest(
        eventId: $event->id,
        registration: registrationPayload(),
        attendees: attendeePayload('ticket-abc-0001'),
        gateway: 'manual',
        token: 'unknown-token',
    );

    expect(fn () => $useCase->execute($request))
        ->toThrow(\DomainException::class, 'Invalid or expired booking token.');
});

test('throws when token belongs to a different event', function () {
    $event = FakeEventFactory::create(3);

    $wrongEventToken = tokenFor(eventId: 999, tokenId: 'wrong-event-token');
    $tokenStore = FakeTokenStore::withToken($wrongEventToken);

    $useCase = makeCreateBookingUseCase(
        FakeEventRepository::one($event),
        FakeBookingRepository::empty(),
        $tokenStore,
    );

    $request = new CreateBookingRequest(
        eventId: $event->id,
        registration: registrationPayload(),
        attendees: attendeePayload('ticket-abc-0001'),
        gateway: 'manual',
        token: 'wrong-event-token',
    );

    expect(fn () => $useCase->execute($request))
        ->toThrow(\DomainException::class, 'Booking token does not match event.');
});

test('throws when session hash does not match token', function () {
    $event = FakeEventFactory::create(4);

    $token = new BookingTokenRecord(
        tokenId: 'session-mismatch-token',
        eventId: $event->id->toInt(),
        sessionHash: 'stored-session-hash',
        expiresAt: new DateTimeImmutable('+1 hour'),
    );
    $tokenStore = FakeTokenStore::withToken($token);

    // Use-case is built with a different session hash than the stored one
    $useCase = makeCreateBookingUseCase(
        FakeEventRepository::one($event),
        FakeBookingRepository::empty(),
        $tokenStore,
        sessionHash: 'completely-different-session-hash',
    );

    $request = new CreateBookingRequest(
        eventId: $event->id,
        registration: registrationPayload(),
        attendees: attendeePayload('ticket-abc-0001'),
        gateway: 'manual',
        token: 'session-mismatch-token',
    );

    expect(fn () => $useCase->execute($request))
        ->toThrow(\DomainException::class, 'Session mismatch.');
});

// ===========================================================================
// Event validation
// ===========================================================================

test('throws when event does not exist', function () {
    $event = FakeEventFactory::create(5);

    $token = tokenFor($event->id->toInt());
    $tokenStore = FakeTokenStore::withToken($token);

    $useCase = makeCreateBookingUseCase(
        FakeEventRepository::empty(), // event not in repository
        FakeBookingRepository::empty(),
        $tokenStore,
    );

    $request = new CreateBookingRequest(
        eventId: $event->id,
        registration: registrationPayload(),
        attendees: attendeePayload('ticket-abc-0001'),
        gateway: 'manual',
        token: 'valid-token',
    );

    expect(fn () => $useCase->execute($request))
        ->toThrow(\DomainException::class, 'Event not found.');
});

test('throws when bookings are disabled for the event', function () {
    $event = FakeEventFactory::create(6, [EventMeta::BOOKING_ENABLED => false]);

    $token = tokenFor($event->id->toInt());
    $tokenStore = FakeTokenStore::withToken($token);

    $useCase = makeCreateBookingUseCase(
        FakeEventRepository::one($event),
        FakeBookingRepository::empty(),
        $tokenStore,
    );

    $ticketId = 'ticket-disabled-001';
    $request = new CreateBookingRequest(
        eventId: $event->id,
        registration: registrationPayload(),
        attendees: attendeePayload($ticketId),
        gateway: 'manual',
        token: 'valid-token',
    );

    expect(fn () => $useCase->execute($request))
        ->toThrow(\DomainException::class, 'Event is not bookable:');
});

test('throws when booking window has not started yet', function () {
    $event = FakeEventFactory::create(7, [
        EventMeta::BOOKING_START => (new DateTimeImmutable('+2 weeks'))->format('Y-m-d H:i:s'),
        EventMeta::BOOKING_END   => (new DateTimeImmutable('+6 weeks'))->format('Y-m-d H:i:s'),
    ]);

    $ticketId = $event->tickets?->toArray()[0]?->id?->toString() ?? 'ticket-1';

    $token = tokenFor($event->id->toInt());
    $tokenStore = FakeTokenStore::withToken($token);

    $useCase = makeCreateBookingUseCase(
        FakeEventRepository::one($event),
        FakeBookingRepository::empty(),
        $tokenStore,
    );

    $request = new CreateBookingRequest(
        eventId: $event->id,
        registration: registrationPayload(),
        attendees: attendeePayload($ticketId),
        gateway: 'manual',
        token: 'valid-token',
    );

    expect(fn () => $useCase->execute($request))
        ->toThrow(\DomainException::class, 'NOT_STARTED');
});

test('throws when booking window has already ended', function () {
    $event = FakeEventFactory::create(8, [
        EventMeta::BOOKING_START => (new DateTimeImmutable('-3 months'))->format('Y-m-d H:i:s'),
        EventMeta::BOOKING_END   => (new DateTimeImmutable('-1 month'))->format('Y-m-d H:i:s'),
    ]);

    $ticketId = $event->tickets?->toArray()[0]?->id?->toString() ?? 'ticket-1';

    $token = tokenFor($event->id->toInt());
    $tokenStore = FakeTokenStore::withToken($token);

    $useCase = makeCreateBookingUseCase(
        FakeEventRepository::one($event),
        FakeBookingRepository::empty(),
        $tokenStore,
    );

    $request = new CreateBookingRequest(
        eventId: $event->id,
        registration: registrationPayload(),
        attendees: attendeePayload($ticketId),
        gateway: 'manual',
        token: 'valid-token',
    );

    expect(fn () => $useCase->execute($request))
        ->toThrow(\DomainException::class, 'ENDED');
});

test('throws when event is completely sold out', function () {
    $ticketId = 'ticket-sold-out-01';

    // Capacity of 2, fully booked via cached stats
    $event = FakeEventFactory::create(9, [
        EventMeta::BOOKING_CAPACITY => 2,
        EventMeta::TICKETS => [[
            'ticket_id'          => $ticketId,
            'ticket_name'        => 'Standard',
            'ticket_description' => '',
            'ticket_price'       => 1000,
            'ticket_spaces'      => 2,
            'ticket_max'         => 5,
            'ticket_min'         => 1,
            'ticket_enabled'     => true,
            'ticket_start'       => (new DateTimeImmutable('-1 month'))->format('Y-m-d H:i:s'),
            'ticket_end'         => (new DateTimeImmutable('+1 month'))->format('Y-m-d H:i:s'),
            'ticket_order'       => 1,
            'ticket_form'        => 1,
        ]],
    ]);

    $formRepository = FakeFormRepository::empty();
    $bookingRepository = FakeBookingRepository::empty();
    // Seed 2 approved bookings to fill the capacity of 2
    $bookingRepository->seedBookingsForEvent($event, $formRepository, 4);

    $token = tokenFor($event->id->toInt());
    $tokenStore = FakeTokenStore::withToken($token);

    $useCase = makeCreateBookingUseCase(
        FakeEventRepository::one($event),
        $bookingRepository,
        $tokenStore,
    );

    $request = new CreateBookingRequest(
        eventId: $event->id,
        registration: registrationPayload(),
        attendees: attendeePayload($ticketId),
        gateway: 'manual',
        token: 'valid-token',
    );

    expect(fn () => $useCase->execute($request))
        ->toThrow(\DomainException::class, 'SOLD_OUT');
});

// ===========================================================================
// Ticket validation
// ===========================================================================

test('throws when specific ticket is sold out', function () {
    $limitedTicketId = 'ticket-limited-001';

    // Two tickets: the limited one (capacity 1) and a regular one (capacity 50).
    // Seeding 1 booking fills only the limited ticket, leaving the regular one
    // available — so the event-level canBookAt() check passes. Only the
    // per-ticket check in CheckTicketAvailibility should reject the request.
    $event = FakeEventFactory::create(10, [
        EventMeta::BOOKING_CAPACITY => 100,
        EventMeta::BOOKING_START => '2026-01-01 00:00:00',
        EventMeta::BOOKING_END => '2027-01-01 00:00:00',
        EventMeta::TICKETS => [
            [
                'ticket_id'          => $limitedTicketId,
                'ticket_name'        => 'Limited',
                'ticket_description' => '',
                'ticket_price'       => 500,
                'ticket_spaces'      => 1, // capacity: 1
                'ticket_max'         => 1,
                'ticket_min'         => 1,
                'ticket_enabled'     => true,
                'ticket_start'       => (new DateTimeImmutable('-1 month'))->format('Y-m-d H:i:s'),
                'ticket_end'         => (new DateTimeImmutable('+1 month'))->format('Y-m-d H:i:s'),
                'ticket_order'       => 1,
                'ticket_form'        => 1,
            ],
            [
                'ticket_id'          => 'ticket-regular-001',
                'ticket_name'        => 'Regular',
                'ticket_description' => '',
                'ticket_price'       => 500,
                'ticket_spaces'      => 50,
                'ticket_max'         => 5,
                'ticket_min'         => 1,
                'ticket_enabled'     => true,
                'ticket_start'       => (new DateTimeImmutable('-1 month'))->format('Y-m-d H:i:s'),
                'ticket_end'         => (new DateTimeImmutable('+1 month'))->format('Y-m-d H:i:s'),
                'ticket_order'       => 2,
                'ticket_form'        => 1,
            ],
        ],
    ]);

    $formRepository = FakeFormRepository::empty();
    $bookingRepository = FakeBookingRepository::empty();
    // 1 booking → goes to ticket[0] (limited) with PENDING status → booked count = 1 = capacity
    $bookingRepository->seedBookingsForEvent($event, $formRepository, 1);

    $token = tokenFor($event->id->toInt());
    $tokenStore = FakeTokenStore::withToken($token);

    $useCase = makeCreateBookingUseCase(
        FakeEventRepository::one($event),
        $bookingRepository,
        $tokenStore,
    );

    $request = new CreateBookingRequest(
        eventId: $event->id,
        registration: registrationPayload(),
        attendees: attendeePayload($limitedTicketId),
        gateway: 'manual',
        token: 'valid-token',
    );

    expect(fn () => $useCase->execute($request))
        ->toThrow(\DomainException::class, 'free space');
});

test('throws when ticket sales period has not started yet', function () {
    $ticketId = 'ticket-future-sale-01';

    $event = FakeEventFactory::create(11, [
        EventMeta::TICKETS => [[
            'ticket_id'          => $ticketId,
            'ticket_name'        => 'Early Bird',
            'ticket_description' => '',
            'ticket_price'       => 800,
            'ticket_spaces'      => 50,
            'ticket_max'         => 5,
            'ticket_min'         => 1,
            'ticket_enabled'     => true,
            'ticket_start'       => (new DateTimeImmutable('+1 week'))->format('Y-m-d H:i:s'), // future
            'ticket_end'         => (new DateTimeImmutable('+2 months'))->format('Y-m-d H:i:s'),
            'ticket_order'       => 1,
            'ticket_form'        => 1,
        ]],
    ]);

    $token = tokenFor($event->id->toInt());
    $tokenStore = FakeTokenStore::withToken($token);

    $useCase = makeCreateBookingUseCase(
        FakeEventRepository::one($event),
        FakeBookingRepository::empty(),
        $tokenStore,
    );

    $request = new CreateBookingRequest(
        eventId: $event->id,
        registration: registrationPayload(),
        attendees: attendeePayload($ticketId),
        gateway: 'manual',
        token: 'valid-token',
    );

    expect(fn () => $useCase->execute($request))
        ->toThrow(\DomainException::class);
});

test('throws when ticket sales period has ended', function () {
    $ticketId = 'ticket-expired-sale-01';

    $event = FakeEventFactory::create(12, [
        EventMeta::TICKETS => [[
            'ticket_id'          => $ticketId,
            'ticket_name'        => 'Early Bird',
            'ticket_description' => '',
            'ticket_price'       => 800,
            'ticket_spaces'      => 50,
            'ticket_max'         => 5,
            'ticket_min'         => 1,
            'ticket_enabled'     => true,
            'ticket_start'       => (new DateTimeImmutable('-3 months'))->format('Y-m-d H:i:s'),
            'ticket_end'         => (new DateTimeImmutable('-1 week'))->format('Y-m-d H:i:s'), // in the past
            'ticket_order'       => 1,
            'ticket_form'        => 1,
        ]],
    ]);

    $token = tokenFor($event->id->toInt());
    $tokenStore = FakeTokenStore::withToken($token);

    $useCase = makeCreateBookingUseCase(
        FakeEventRepository::one($event),
        FakeBookingRepository::empty(),
        $tokenStore,
    );

    $request = new CreateBookingRequest(
        eventId: $event->id,
        registration: registrationPayload(),
        attendees: attendeePayload($ticketId),
        gateway: 'manual',
        token: 'valid-token',
    );

    expect(fn () => $useCase->execute($request))
        ->toThrow(\DomainException::class, 'currently not available');
});

test('throws when an unknown ticket id is submitted', function () {
    $event = FakeEventFactory::create(13);

    $token = tokenFor($event->id->toInt());
    $tokenStore = FakeTokenStore::withToken($token);

    $useCase = makeCreateBookingUseCase(
        FakeEventRepository::one($event),
        FakeBookingRepository::empty(),
        $tokenStore,
    );

    $request = new CreateBookingRequest(
        eventId: $event->id,
        registration: registrationPayload(),
        attendees: attendeePayload('ticket-does-not-exist'),
        gateway: 'manual',
        token: 'valid-token',
    );

    expect(fn () => $useCase->execute($request))
        ->toThrow(\DomainException::class, 'Ticket not found');
});

// ===========================================================================
// Payment validation
// ===========================================================================

test('throws when payment gateway is not found for a paid booking', function () {
    $ticketId = 'ticket-paid-001';

    $event = FakeEventFactory::create(14, [
        EventMeta::TICKETS => [[
            'ticket_id'          => $ticketId,
            'ticket_name'        => 'Paid Ticket',
            'ticket_description' => '',
            'ticket_price'       => 2000, // non-free
            'ticket_spaces'      => 50,
            'ticket_max'         => 5,
            'ticket_min'         => 1,
            'ticket_enabled'     => true,
            'ticket_start'       => (new DateTimeImmutable('-1 month'))->format('Y-m-d H:i:s'),
            'ticket_end'         => (new DateTimeImmutable('+1 month'))->format('Y-m-d H:i:s'),
            'ticket_order'       => 1,
            'ticket_form'        => 1,
        ]],
    ]);

    $token = tokenFor($event->id->toInt());
    $tokenStore = FakeTokenStore::withToken($token);

    $useCase = makeCreateBookingUseCase(
        FakeEventRepository::one($event),
        FakeBookingRepository::empty(),
        $tokenStore,
        gatewayRepository: FakeGatewayRepository::empty(), // no gateway configured
    );

    $request = new CreateBookingRequest(
        eventId: $event->id,
        registration: registrationPayload(),
        attendees: attendeePayload($ticketId),
        gateway: 'stripe',
        token: 'valid-token',
    );

    expect(fn () => $useCase->execute($request))
        ->toThrow(\DomainException::class, 'Payment gateway not found');
});

test('throws when donation is submitted but event does not accept donations', function () {
    $ticketId = 'ticket-free-001';

    $event = FakeEventFactory::create(15, [
        EventMeta::DONATION_ENABLED => false,
        EventMeta::TICKETS => [[
            'ticket_id'          => $ticketId,
            'ticket_name'        => 'Free Ticket',
            'ticket_description' => '',
            'ticket_price'       => 0,
            'ticket_spaces'      => 50,
            'ticket_max'         => 5,
            'ticket_min'         => 1,
            'ticket_enabled'     => true,
            'ticket_start'       => (new DateTimeImmutable('-1 month'))->format('Y-m-d H:i:s'),
            'ticket_end'         => (new DateTimeImmutable('+1 month'))->format('Y-m-d H:i:s'),
            'ticket_order'       => 1,
            'ticket_form'        => 1,
        ]],
    ]);

    $token = tokenFor($event->id->toInt());
    $tokenStore = FakeTokenStore::withToken($token);

    $useCase = makeCreateBookingUseCase(
        FakeEventRepository::one($event),
        FakeBookingRepository::empty(),
        $tokenStore,
    );

    $request = new CreateBookingRequest(
        eventId: $event->id,
        registration: registrationPayload(),
        attendees: attendeePayload($ticketId),
        gateway: 'manual',
        token: 'valid-token',
        donationAmount: 500,
    );

    expect(fn () => $useCase->execute($request))
        ->toThrow(\DomainException::class, 'does not accept donations');
});
