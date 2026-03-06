<?php
declare(strict_types=1);

namespace Tests\Support;

use Contexis\Events\Booking\Domain\Attendee;
use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Booking\Domain\ValueObjects\TicketBookings;
use Contexis\Events\Booking\Domain\ValueObjects\TicketBookingsMap;
use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\Ticket;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Domain\ValueObjects\TicketId;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;

final class FakeBookingRepository implements BookingRepository
{
    /** @var array<int, Booking> */
    private array $bookingsById = [];

    /** @var array<int, int[]> */
    private array $bookingIdsByEventId = [];

    private int $sequence = 1;

    public ?BookingId $lastFindArg = null;
    public ?EventId $lastEventIdArg = null;

    public static function empty(): self
    {
        return new self();
    }

    public static function withDemoBookingsForEvent(Event $event, FakeFormRepository $formRepository, int $count = 4): self
    {
        $repository = new self();
        $repository->seedBookingsForEvent($event, $formRepository, $count);

        return $repository;
    }

    public function find(BookingId $id): ?Booking
    {
        $this->lastFindArg = $id;

        return $this->bookingsById[$id->toInt()] ?? null;
    }

    public function save(Booking $booking): BookingId
    {
        $bookingId = BookingId::from($this->sequence) ?? throw new \RuntimeException('Invalid id');
        $this->bookingsById[$bookingId->toInt()] = $booking->withId($bookingId);
        $this->bookingIdsByEventId[$booking->eventId->toInt()][] = $bookingId->toInt();
        $this->sequence++;
        return $bookingId;
    }

    public function getTicketBookingsForEvent(EventId $eventId, array $ticketIds = []): TicketBookingsMap
    {
        $this->lastEventIdArg = $eventId;

        $counts = $this->initializeCounts($ticketIds);

        foreach ($this->bookingsForEvent($eventId) as $booking) {
            foreach ($booking->attendees as $attendee) {
                $ticketId = $attendee->ticketId->toString();

                if ($ticketIds !== [] && !in_array($ticketId, $ticketIds, true)) {
                    continue;
                }

                if (!isset($counts[$ticketId])) {
                    $counts[$ticketId] = ['pending' => 0, 'approved' => 0, 'canceled' => 0, 'expired' => 0];
                }

                $counts[$ticketId][$this->statusKey($booking->status)]++;
            }
        }

        $items = [];
        foreach ($counts as $ticketId => $stats) {
            $resolvedTicketId = TicketId::from($ticketId);
            if ($resolvedTicketId === null) {
                continue;
            }

            $items[] = new TicketBookings(
                ticketId: $resolvedTicketId,
                pending: $stats['pending'],
                approved: $stats['approved'],
                canceled: $stats['canceled'],
                expired: $stats['expired']
            );
        }

        return new TicketBookingsMap($items);
    }

    public function seedBookingsForEvent(Event $event, FakeFormRepository $formRepository, int $count = 4): void
    {
        if ($count <= 0) {
            return;
        }

        $bookingFormId = $event->forms?->bookingForm;
        if ($bookingFormId !== null) {
            $formRepository->ensureBookingForm($bookingFormId);
        }

        $attendeeFormId = $event->forms?->attendeeForm;
        if ($attendeeFormId !== null) {
            $formRepository->ensureAttendeeForm($attendeeFormId);
        }

        $tickets = $event->tickets?->toArray() ?? [];
        if ($tickets === []) {
            $ticketId = TicketId::from('ticket-default-1');
            if ($ticketId === null) {
                return;
            }

            $tickets = [
                new Ticket(
                    id: $ticketId,
                    name: 'Default Ticket',
                    description: null,
                    price: $event->getLowestAvailablePrice(new \DateTimeImmutable()) ?? $this->fallbackPrice(),
                    capacity: null,
                    enabled: true,
                    salesStart: null,
                    salesEnd: null,
                    order: 1,
                    form: $attendeeFormId?->toInt() ?? 0,
                    min: 1,
                    max: 10
                ),
            ];
        }

        for ($index = 0; $index < $count; $index++) {
            $ticket = $tickets[$index % count($tickets)];
            $status = $this->statusForIndex($index);

            $booking = new Booking(
                reference: BookingReference::fromString($this->nextReference()),
                email: new Email("booking{$this->sequence}@example.test"),
                name: PersonName::from('Test', 'User ' . $this->sequence),
                priceSummary: PriceSummary::fromValues(
                    bookingPrice: $ticket->price->toInt(),
                    donationAmount: 0,
                    discountAmount: 0,
                    currency: $ticket->price->currency->toString()
                ),
                bookingTime: new \DateTimeImmutable('now'),
                status: $status,
                registration: new RegistrationData([
                    'booking_form_id' => $bookingFormId?->toInt(),
                    'email' => "booking{$this->sequence}@example.test",
                    'first_name' => 'Test',
                    'last_name' => 'User ' . $this->sequence,
                ]),
                attendees: new AttendeeCollection(
                    new Attendee(
                        ticketId: $ticket->id,
                        ticketPrice: $ticket->price,
                        firstName: 'Attendee',
                        lastName: (string) ($index + 1),
                        birthDate: null,
                        metadata: ['attendee_form_id' => $attendeeFormId?->toInt()]
                    )
                ),
                gateway: 'manual',
                coupon: null,
                transactions: null,
                eventId: $event->id
            );

            $bookingId = BookingId::from($this->sequence);
            if ($bookingId === null) {
                $this->sequence++;
                continue;
            }

            $this->bookingsById[$bookingId->toInt()] = $booking;
            $this->bookingIdsByEventId[$event->id->toInt()][] = $bookingId->toInt();
            $this->sequence++;
        }
    }

    /** @return Booking[] */
    private function bookingsForEvent(EventId $eventId): array
    {
        $bookingIds = $this->bookingIdsByEventId[$eventId->toInt()] ?? [];
        $bookings = [];

        foreach ($bookingIds as $bookingId) {
            if (!isset($this->bookingsById[$bookingId])) {
                continue;
            }

            $bookings[] = $this->bookingsById[$bookingId];
        }

        return $bookings;
    }

    /** @param string[] $ticketIds */
    private function initializeCounts(array $ticketIds): array
    {
        $counts = [];

        foreach ($ticketIds as $ticketId) {
            if (!is_string($ticketId) || $ticketId === '') {
                continue;
            }

            $counts[$ticketId] = ['pending' => 0, 'approved' => 0, 'canceled' => 0, 'expired' => 0];
        }

        return $counts;
    }

    private function statusForIndex(int $index): BookingStatus
    {
        return match ($index % 4) {
            0 => BookingStatus::PENDING,
            1 => BookingStatus::APPROVED,
            2 => BookingStatus::APPROVED,
            default => BookingStatus::CANCELED,
        };
    }

    private function statusKey(BookingStatus $status): string
    {
        return match ($status) {
            BookingStatus::PENDING => 'pending',
            BookingStatus::APPROVED => 'approved',
            BookingStatus::CANCELED => 'canceled',
            BookingStatus::EXPIRED => 'expired',
            BookingStatus::DELETED => 'expired',
        };
    }

    private function nextReference(): string
    {
        return str_pad((string) $this->sequence, 12, '0', STR_PAD_LEFT);
    }

    private function fallbackPrice(): \Contexis\Events\Shared\Domain\ValueObjects\Price
    {
        return \Contexis\Events\Shared\Domain\ValueObjects\Price::from(
            0,
            \Contexis\Events\Shared\Domain\ValueObjects\Currency::fromCode('EUR')
        );
    }
}
