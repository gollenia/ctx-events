<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Domain;

use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Booking\Domain\ValueObjects\TicketBookingsMap;
use Contexis\Events\Event\Domain\ValueObjects\EventId;

interface BookingRepository
{
    public function find(BookingId $id): ?Booking;

    public function save(Booking $booking): BookingId;

	/** @param string[] $ticketIds */
	public function getTicketBookingsForEvent(EventId $eventId, array $ticketIds = []): TicketBookingsMap;

	public function getTicketBookingsForEvents(array $eventIds): array;
}
