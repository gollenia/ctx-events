<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Domain;

use Contexis\Events\Booking\Application\DTOs\BookingListRequest;
use Contexis\Events\Booking\Application\DTOs\BookingListResponse;
use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Booking\Domain\ValueObjects\BookingNotesCollection;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\LogEntryCollection;
use Contexis\Events\Booking\Domain\ValueObjects\TicketBookingsMap;
use Contexis\Events\Event\Domain\ValueObjects\EventId;

interface BookingRepository
{
    public function find(BookingId $id): ?Booking;

    public function findByReference(string $reference): ?Booking;

	public function findByEventId(EventId $eventId): array;

    public function save(Booking $booking): BookingId;

    public function update(Booking $booking): void;

    public function updateNotes(BookingId $id, BookingNotesCollection $notes): void;

    public function search(BookingListRequest $query): BookingListResponse;

    public function delete(BookingId $id): void;

    public function updateStatus(BookingId $id, BookingStatus $status, LogEntryCollection $logEntries): void;

    /** @param string[] $ticketIds */
    public function getTicketBookingsForEvent(EventId $eventId, array $ticketIds = []): TicketBookingsMap;

    public function getTicketBookingsForEvents(array $eventIds): array;
}
