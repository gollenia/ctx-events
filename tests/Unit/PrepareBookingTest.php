<?php
declare(strict_types=1);

use Contexis\Events\Booking\Application\Services\IssueBookingToken;
use Contexis\Events\Booking\Domain\BookingTokenStore;
use Contexis\Events\Booking\Domain\ValueObjects\BookingTokenRecord;
use Contexis\Events\Event\Application\DTOs\PrepareBookingResponse;
use Contexis\Events\Event\Application\Service\EventPolicy;
use Contexis\Events\Event\Application\Service\PrepareBookingTicketLimits;
use Contexis\Events\Event\Application\UseCases\PrepareBooking;
use Tests\Support\FakeGatewayRepository;
use Contexis\Events\Shared\Application\ValueObjects\UserContext;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\Contracts\SessionHashResolver;
use Contexis\Events\Shared\Domain\Contracts\TokenGenerator;
use Tests\Support\FakeBookingRepository;
use Tests\Support\FakeEventFactory;
use Tests\Support\FakeEventRepository;
use Tests\Support\FakeFormRepository;

test('prepare booking returns response with forms, tickets and token', function () {
    $event = FakeEventFactory::create(77);

    $eventRepository = FakeEventRepository::one($event);
    $formRepository = FakeFormRepository::empty();

    $bookingRepository = FakeBookingRepository::empty();
    $bookingRepository->seedBookingsForEvent($event, $formRepository, 4);

    $gatewayRepository = FakeGatewayRepository::withActiveGateway();

    $eventPolicy = Mockery::mock(EventPolicy::class);
    $eventPolicy->shouldReceive('userCanView')
        ->once()
        ->andReturn(true);

    $tokenStore = Mockery::mock(BookingTokenStore::class);
    $tokenStore->shouldReceive('save')
        ->once()
        ->with(Mockery::on(static fn (BookingTokenRecord $record): bool => $record->eventId === $event->id->toInt()));

    $tokenGenerator = Mockery::mock(TokenGenerator::class);
    $tokenGenerator->shouldReceive('generate')
        ->once()
        ->andReturn('token-prepare-booking');

    $sessionHashResolver = Mockery::mock(SessionHashResolver::class);
    $sessionHashResolver->shouldReceive('resolve')
        ->once()
        ->andReturn('session-hash-test');

    $issueBookingToken = new IssueBookingToken($tokenStore, $tokenGenerator, $sessionHashResolver);

    $clock = Mockery::mock(Clock::class);
    $clock->shouldReceive('now')
        ->once()
        ->andReturn(new DateTimeImmutable('2026-03-04 10:00:00'));

    $useCase = new PrepareBooking(
        eventRepository: $eventRepository,
        bookingRepository: $bookingRepository,
        gatewayRepository: $gatewayRepository,
        eventPolicy: $eventPolicy,
        formRepository: $formRepository,
        issueBookingToken: $issueBookingToken,
        prepareBookingTicketLimits: new PrepareBookingTicketLimits(),
        clock: $clock,
    );

    $response = $useCase->execute($event->id->toInt(), new UserContext(0, false, false, false));

    expect($response)->toBeInstanceOf(PrepareBookingResponse::class);
    expect($response)->not->toBeNull();
    expect($response->eventName)->toBe($event->name);
    expect($response->bookingForm)->not->toBeNull();
    expect($response->attendeeForm)->not->toBeNull();
    expect($response->tickets->count())->toBeGreaterThan(0);
    expect($response->token)->toBe('token-prepare-booking');
});
