<?php

declare(strict_types=1);

use Contexis\Events\Booking\Application\DTOs\AddBookingAttendeeRequest;
use Contexis\Events\Booking\Application\Services\AttendeeFactory;
use Contexis\Events\Booking\Application\UseCases\AddBookingAttendee;
use Contexis\Events\Booking\Domain\Attendee;
use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\Services\CalculateBookingPrice;
use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Event\Application\Service\PrepareBookingTicketLimits;
use Contexis\Events\Event\Infrastructure\EventMeta;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Tests\Support\FakeBookingRepository;
use Tests\Support\FakeCurrentActorProvider;
use Tests\Support\FakeEventFactory;
use Tests\Support\FakeEventRepository;

function makeAddAttendeeClock(string $now = '2026-03-16 12:00:00'): Clock
{
	$clock = Mockery::mock(Clock::class);
	$clock->shouldReceive('now')->andReturn(new DateTimeImmutable($now));

	return $clock;
}

it('adds an attendee and recalculates the booking price', function () {
	$event = FakeEventFactory::create(301);
	$ticket = $event->tickets?->toArray()[0] ?? throw new RuntimeException('Missing ticket');
	$bookingRepository = FakeBookingRepository::empty();

	$bookingId = $bookingRepository->save(new Booking(
		reference: BookingReference::fromString('BOOK-ADD-001'),
		email: new Email('booking@example.test'),
		name: PersonName::from('Casey', 'Customer'),
		priceSummary: PriceSummary::fromValues(
			bookingPrice: $ticket->price,
			donationAmount: $ticket->price->withAmount(0),
			discountAmount: $ticket->price->withAmount(0),
		),
		bookingTime: new DateTimeImmutable('2026-03-17 10:00:00'),
		status: BookingStatus::APPROVED,
		registration: new RegistrationData([
			'email' => 'booking@example.test',
			'first_name' => 'Casey',
			'last_name' => 'Customer',
		]),
		attendees: AttendeeCollection::from(
			new Attendee(
				ticketId: $ticket->id,
				ticketPrice: $ticket->price,
				name: PersonName::from('Alex', 'Original'),
				metadata: ['first_name' => 'Alex', 'last_name' => 'Original'],
			),
		),
		gateway: 'manual',
		coupon: null,
		transactions: null,
		eventId: $event->id,
	));

	$useCase = new AddBookingAttendee(
		bookingRepository: $bookingRepository,
		eventRepository: FakeEventRepository::one($event),
		attendeeFactory: new AttendeeFactory(),
		prepareBookingTicketLimits: new PrepareBookingTicketLimits(),
		calculateBookingPrice: new CalculateBookingPrice(),
		clock: makeAddAttendeeClock(),
		currentActorProvider: new FakeCurrentActorProvider(),
	);

	$useCase->execute(new AddBookingAttendeeRequest(
		reference: 'BOOK-ADD-001',
		attendee: [
			'ticketId' => $ticket->id->toString(),
			'ticketPrice' => [
				'amountCents' => $ticket->price->amountCents,
				'currency' => $ticket->price->currency->toString(),
			],
			'name' => [
				'firstName' => 'Jamie',
				'lastName' => 'New',
			],
			'metadata' => [
				'first_name' => 'Jamie',
				'last_name' => 'New',
			],
			'status' => 'active',
		],
	));

	$updatedBooking = $bookingRepository->find($bookingId);

	expect($updatedBooking)->not->toBeNull();
	expect(count($updatedBooking->attendees))->toBe(2);
	expect($updatedBooking->priceSummary->bookingPrice->amountCents)->toBe($ticket->price->amountCents * 2);
	expect($updatedBooking->priceSummary->finalPrice->amountCents)->toBe($ticket->price->amountCents * 2);
});

it('blocks adding an attendee when the selected ticket has no seats left', function () {
	$event = FakeEventFactory::create(302, [
		EventMeta::BOOKING_CAPACITY => 1,
		EventMeta::TICKETS => [
			[
				'ticket_id' => 'ticket-main-302',
				'ticket_name' => 'Standard',
				'ticket_description' => 'Standard ticket',
				'ticket_price' => 1500,
				'ticket_spaces' => 1,
				'ticket_max' => 10,
				'ticket_min' => 1,
				'ticket_enabled' => true,
				'ticket_start' => '2026-03-01 00:00:00',
				'ticket_end' => '2026-03-31 23:59:59',
				'ticket_order' => 1,
				'ticket_form' => 1,
			],
		],
	]);
	$ticket = $event->tickets?->toArray()[0] ?? throw new RuntimeException('Missing ticket');
	$bookingRepository = FakeBookingRepository::empty();

	$bookingRepository->save(new Booking(
		reference: BookingReference::fromString('BOOK-ADD-002'),
		email: new Email('booking2@example.test'),
		name: PersonName::from('Casey', 'Customer'),
		priceSummary: PriceSummary::fromValues(
			bookingPrice: $ticket->price,
			donationAmount: $ticket->price->withAmount(0),
			discountAmount: $ticket->price->withAmount(0),
		),
		bookingTime: new DateTimeImmutable('2026-03-17 10:00:00'),
		status: BookingStatus::APPROVED,
		registration: new RegistrationData([
			'email' => 'booking2@example.test',
			'first_name' => 'Casey',
			'last_name' => 'Customer',
		]),
		attendees: AttendeeCollection::from(
			new Attendee(
				ticketId: $ticket->id,
				ticketPrice: $ticket->price,
				name: PersonName::from('Alex', 'Only'),
				metadata: ['first_name' => 'Alex', 'last_name' => 'Only'],
			),
		),
		gateway: 'manual',
		coupon: null,
		transactions: null,
		eventId: $event->id,
	));

	$useCase = new AddBookingAttendee(
		bookingRepository: $bookingRepository,
		eventRepository: FakeEventRepository::one($event),
		attendeeFactory: new AttendeeFactory(),
		prepareBookingTicketLimits: new PrepareBookingTicketLimits(),
		calculateBookingPrice: new CalculateBookingPrice(),
		clock: makeAddAttendeeClock(),
		currentActorProvider: new FakeCurrentActorProvider(),
	);

	expect(fn () => $useCase->execute(new AddBookingAttendeeRequest(
		reference: 'BOOK-ADD-002',
		attendee: [
			'ticketId' => $ticket->id->toString(),
			'ticketPrice' => [
				'amountCents' => $ticket->price->amountCents,
				'currency' => $ticket->price->currency->toString(),
			],
			'name' => [
				'firstName' => 'Jamie',
				'lastName' => 'Blocked',
			],
			'metadata' => [
				'first_name' => 'Jamie',
				'last_name' => 'Blocked',
			],
			'status' => 'active',
		],
	)))->toThrow(DomainException::class, 'No seats available for the selected ticket.');
});
