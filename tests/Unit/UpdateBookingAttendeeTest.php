<?php

declare(strict_types=1);

use Contexis\Events\Booking\Application\DTOs\UpdateBookingAttendeeRequest;
use Contexis\Events\Booking\Application\Services\AttendeeFactory;
use Contexis\Events\Booking\Application\UseCases\UpdateBookingAttendee;
use Contexis\Events\Booking\Domain\Attendee;
use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\Enums\AttendeeStatus;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\Services\CalculateBookingPrice;
use Contexis\Events\Booking\Domain\ValueObjects\AttendeeId;
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

function makeUpdateAttendeeClock(string $now = '2026-03-16 12:00:00'): Clock
{
	$clock = Mockery::mock(Clock::class);
	$clock->shouldReceive('now')->andReturn(new DateTimeImmutable($now));

	return $clock;
}

it('updates attendee metadata without changing the ticket', function () {
	$event = FakeEventFactory::create(401);
	$ticket = $event->tickets?->toArray()[0] ?? throw new RuntimeException('Missing ticket');
	$repository = FakeBookingRepository::empty();

	$bookingId = $repository->save(new Booking(
		reference: BookingReference::fromString('BOOK-UPD-001'),
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
				status: AttendeeStatus::ACTIVE,
				id: AttendeeId::from(41),
			),
		),
		gateway: 'manual',
		coupon: null,
		transactions: null,
		eventId: $event->id,
	));

	$useCase = new UpdateBookingAttendee(
		bookingRepository: $repository,
		eventRepository: FakeEventRepository::one($event),
		attendeeFactory: new AttendeeFactory(),
		prepareBookingTicketLimits: new PrepareBookingTicketLimits(),
		calculateBookingPrice: new CalculateBookingPrice(),
		clock: makeUpdateAttendeeClock(),
		currentActorProvider: new FakeCurrentActorProvider(),
	);

	$useCase->execute(new UpdateBookingAttendeeRequest(
		reference: 'BOOK-UPD-001',
		attendeeId: 41,
		attendee: [
			'id' => 41,
			'ticketId' => $ticket->id->toString(),
			'ticketPrice' => [
				'amountCents' => $ticket->price->amountCents,
				'currency' => $ticket->price->currency->toString(),
			],
			'name' => [
				'firstName' => 'Alex',
				'lastName' => 'Updated',
			],
			'metadata' => [
				'first_name' => 'Alex',
				'last_name' => 'Updated',
			],
			'status' => 'active',
		],
	));

	$updatedBooking = $repository->find($bookingId);
	$updatedAttendee = $updatedBooking?->attendees->getById(AttendeeId::from(41));

	expect($updatedAttendee?->name?->lastName)->toBe('Updated');
	expect($updatedBooking?->priceSummary->finalPrice->amountCents)->toBe($ticket->price->amountCents);
});

it('changes the attendee ticket when the target ticket still has capacity', function () {
	$event = FakeEventFactory::create(402, [
		EventMeta::TICKETS => [
			[
				'ticket_id' => 'ticket-a',
				'ticket_name' => 'Standard',
				'ticket_description' => 'Standard ticket',
				'ticket_price' => 1500,
				'ticket_spaces' => 5,
				'ticket_max' => 10,
				'ticket_min' => 1,
				'ticket_enabled' => true,
				'ticket_start' => '2026-03-01 00:00:00',
				'ticket_end' => '2026-03-31 23:59:59',
				'ticket_order' => 1,
				'ticket_form' => 1,
			],
			[
				'ticket_id' => 'ticket-b',
				'ticket_name' => 'Premium',
				'ticket_description' => 'Premium ticket',
				'ticket_price' => 2200,
				'ticket_spaces' => 2,
				'ticket_max' => 10,
				'ticket_min' => 1,
				'ticket_enabled' => true,
				'ticket_start' => '2026-03-01 00:00:00',
				'ticket_end' => '2026-03-31 23:59:59',
				'ticket_order' => 2,
				'ticket_form' => 1,
			],
		],
	]);
	$tickets = $event->tickets?->toArray() ?? throw new RuntimeException('Missing tickets');
	[$ticketA, $ticketB] = $tickets;
	$repository = FakeBookingRepository::empty();

	$bookingId = $repository->save(new Booking(
		reference: BookingReference::fromString('BOOK-UPD-002'),
		email: new Email('booking2@example.test'),
		name: PersonName::from('Casey', 'Customer'),
		priceSummary: PriceSummary::fromValues(
			bookingPrice: $ticketA->price,
			donationAmount: $ticketA->price->withAmount(0),
			discountAmount: $ticketA->price->withAmount(0),
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
				ticketId: $ticketA->id,
				ticketPrice: $ticketA->price,
				name: PersonName::from('Alex', 'Original'),
				metadata: ['first_name' => 'Alex', 'last_name' => 'Original'],
				status: AttendeeStatus::ACTIVE,
				id: AttendeeId::from(42),
			),
		),
		gateway: 'manual',
		coupon: null,
		transactions: null,
		eventId: $event->id,
	));

	$useCase = new UpdateBookingAttendee(
		bookingRepository: $repository,
		eventRepository: FakeEventRepository::one($event),
		attendeeFactory: new AttendeeFactory(),
		prepareBookingTicketLimits: new PrepareBookingTicketLimits(),
		calculateBookingPrice: new CalculateBookingPrice(),
		clock: makeUpdateAttendeeClock(),
		currentActorProvider: new FakeCurrentActorProvider(),
	);

	$useCase->execute(new UpdateBookingAttendeeRequest(
		reference: 'BOOK-UPD-002',
		attendeeId: 42,
		attendee: [
			'id' => 42,
			'ticketId' => $ticketB->id->toString(),
			'ticketPrice' => [
				'amountCents' => $ticketB->price->amountCents,
				'currency' => $ticketB->price->currency->toString(),
			],
			'name' => [
				'firstName' => 'Alex',
				'lastName' => 'Original',
			],
			'metadata' => [
				'first_name' => 'Alex',
				'last_name' => 'Original',
			],
			'status' => 'active',
		],
	));

	$updatedBooking = $repository->find($bookingId);
	$updatedAttendee = $updatedBooking?->attendees->getById(AttendeeId::from(42));

	expect($updatedAttendee?->ticketId->toString())->toBe('ticket-b');
	expect($updatedBooking?->priceSummary->finalPrice->amountCents)->toBe(2200);
});

it('blocks changing to a sold out ticket but still allows staying on the current sold out ticket', function () {
	$event = FakeEventFactory::create(403, [
		EventMeta::BOOKING_CAPACITY => 2,
		EventMeta::TICKETS => [
			[
				'ticket_id' => 'ticket-a',
				'ticket_name' => 'Standard',
				'ticket_description' => 'Standard ticket',
				'ticket_price' => 1500,
				'ticket_spaces' => 2,
				'ticket_max' => 10,
				'ticket_min' => 1,
				'ticket_enabled' => true,
				'ticket_start' => '2026-03-01 00:00:00',
				'ticket_end' => '2026-03-31 23:59:59',
				'ticket_order' => 1,
				'ticket_form' => 1,
			],
			[
				'ticket_id' => 'ticket-b',
				'ticket_name' => 'Premium',
				'ticket_description' => 'Premium ticket',
				'ticket_price' => 2200,
				'ticket_spaces' => 1,
				'ticket_max' => 10,
				'ticket_min' => 1,
				'ticket_enabled' => true,
				'ticket_start' => '2026-03-01 00:00:00',
				'ticket_end' => '2026-03-31 23:59:59',
				'ticket_order' => 2,
				'ticket_form' => 1,
			],
		],
	]);
	[$ticketA, $ticketB] = $event->tickets?->toArray() ?? throw new RuntimeException('Missing tickets');
	$repository = FakeBookingRepository::empty();

	$repository->save(new Booking(
		reference: BookingReference::fromString('BOOK-UPD-003-A'),
		email: new Email('a@example.test'),
		name: PersonName::from('A', 'One'),
		priceSummary: PriceSummary::fromValues(
			bookingPrice: $ticketA->price,
			donationAmount: $ticketA->price->withAmount(0),
			discountAmount: $ticketA->price->withAmount(0),
		),
		bookingTime: new DateTimeImmutable('2026-03-17 10:00:00'),
		status: BookingStatus::APPROVED,
		registration: new RegistrationData([
			'email' => 'a@example.test',
			'first_name' => 'A',
			'last_name' => 'One',
		]),
		attendees: AttendeeCollection::from(
			new Attendee(
				ticketId: $ticketB->id,
				ticketPrice: $ticketB->price,
				name: PersonName::from('Sold', 'Out'),
				metadata: ['first_name' => 'Sold', 'last_name' => 'Out'],
				status: AttendeeStatus::ACTIVE,
				id: AttendeeId::from(51),
			),
		),
		gateway: 'manual',
		coupon: null,
		transactions: null,
		eventId: $event->id,
	));

	$bookingId = $repository->save(new Booking(
		reference: BookingReference::fromString('BOOK-UPD-003-B'),
		email: new Email('b@example.test'),
		name: PersonName::from('B', 'Two'),
		priceSummary: PriceSummary::fromValues(
			bookingPrice: $ticketA->price,
			donationAmount: $ticketA->price->withAmount(0),
			discountAmount: $ticketA->price->withAmount(0),
		),
		bookingTime: new DateTimeImmutable('2026-03-17 10:00:00'),
		status: BookingStatus::APPROVED,
		registration: new RegistrationData([
			'email' => 'b@example.test',
			'first_name' => 'B',
			'last_name' => 'Two',
		]),
		attendees: AttendeeCollection::from(
			new Attendee(
				ticketId: $ticketA->id,
				ticketPrice: $ticketA->price,
				name: PersonName::from('Alex', 'Editable'),
				metadata: ['first_name' => 'Alex', 'last_name' => 'Editable'],
				status: AttendeeStatus::ACTIVE,
				id: AttendeeId::from(52),
			),
		),
		gateway: 'manual',
		coupon: null,
		transactions: null,
		eventId: $event->id,
	));

	$useCase = new UpdateBookingAttendee(
		bookingRepository: $repository,
		eventRepository: FakeEventRepository::one($event),
		attendeeFactory: new AttendeeFactory(),
		prepareBookingTicketLimits: new PrepareBookingTicketLimits(),
		calculateBookingPrice: new CalculateBookingPrice(),
		clock: makeUpdateAttendeeClock(),
		currentActorProvider: new FakeCurrentActorProvider(),
	);

	expect(fn () => $useCase->execute(new UpdateBookingAttendeeRequest(
		reference: 'BOOK-UPD-003-B',
		attendeeId: 52,
		attendee: [
			'id' => 52,
			'ticketId' => $ticketB->id->toString(),
			'ticketPrice' => [
				'amountCents' => $ticketB->price->amountCents,
				'currency' => $ticketB->price->currency->toString(),
			],
			'name' => [
				'firstName' => 'Alex',
				'lastName' => 'Editable',
			],
			'metadata' => [
				'first_name' => 'Alex',
				'last_name' => 'Editable',
			],
			'status' => 'active',
		],
	)))->toThrow(DomainException::class, 'No seats available for the selected ticket.');

	$repository->save(new Booking(
		reference: BookingReference::fromString('BOOK-UPD-003-C'),
		email: new Email('c@example.test'),
		name: PersonName::from('C', 'Three'),
		priceSummary: PriceSummary::fromValues(
			bookingPrice: $ticketB->price,
			donationAmount: $ticketB->price->withAmount(0),
			discountAmount: $ticketB->price->withAmount(0),
		),
		bookingTime: new DateTimeImmutable('2026-03-17 10:00:00'),
		status: BookingStatus::APPROVED,
		registration: new RegistrationData([
			'email' => 'c@example.test',
			'first_name' => 'C',
			'last_name' => 'Three',
		]),
		attendees: AttendeeCollection::from(
			new Attendee(
				ticketId: $ticketB->id,
				ticketPrice: $ticketB->price,
				name: PersonName::from('Same', 'Ticket'),
				metadata: ['first_name' => 'Same', 'last_name' => 'Ticket'],
				status: AttendeeStatus::ACTIVE,
				id: AttendeeId::from(53),
			),
		),
		gateway: 'manual',
		coupon: null,
		transactions: null,
		eventId: $event->id,
	));

	$useCase->execute(new UpdateBookingAttendeeRequest(
		reference: 'BOOK-UPD-003-C',
		attendeeId: 53,
		attendee: [
			'id' => 53,
			'ticketId' => $ticketB->id->toString(),
			'ticketPrice' => [
				'amountCents' => $ticketB->price->amountCents,
				'currency' => $ticketB->price->currency->toString(),
			],
			'name' => [
				'firstName' => 'Same',
				'lastName' => 'Updated',
			],
			'metadata' => [
				'first_name' => 'Same',
				'last_name' => 'Updated',
			],
			'status' => 'active',
		],
	));

	$updatedBooking = $repository->findByReference('BOOK-UPD-003-C');
	expect($updatedBooking?->attendees->getById(AttendeeId::from(53))?->name?->lastName)->toBe('Updated');
});
