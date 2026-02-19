<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Booking\Domain\ValueObjects\TicketBookingsMap;
use Contexis\Events\Booking\Domain\ValueObjects\TicketSalesStats;
use Contexis\Events\Event\Domain\Enums\BookingDenyReason;
use Contexis\Events\Event\Domain\ValueObjects\BookingDecision;
use Contexis\Events\Event\Domain\ValueObjects\BookingPolicy;
use Contexis\Events\Event\Domain\ValueObjects\EventForms;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Domain\ValueObjects\EventSpaces;
use Contexis\Events\Event\Domain\ValueObjects\EventViewConfig;
use Contexis\Events\Location\Domain\LocationId;
use Contexis\Events\Media\Domain\ImageId;
use Contexis\Events\Person\Domain\PersonId;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceId;
use Contexis\Events\Shared\Domain\ValueObjects\Status;
use Contexis\Events\Shared\Domain\Traits\HasStatus;
use Contexis\Events\Shared\Domain\ValueObjects\AuthorId;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use DateTimeImmutable;

final readonly class Event
{
    use HasStatus;

    public function __construct(
        public EventId $id,
        public Status $status,
        public string $name,
        public DateTimeImmutable $startDate,
        public DateTimeImmutable $endDate,
        public DateTimeImmutable $createdAt,
        public EventViewConfig $eventViewConfig,
        public AuthorId $authorId,
		public EventForms $forms,
		public ?string $description = null,
        public ?string $audience = null,
		public ?int $overallCapacity = null,
		public ?BookingPolicy $bookingPolicy = null,
        public ?TicketCollection $tickets = null,
		public ?TicketBookingsMap $ticketBookingsMap = null,
        public ?LocationId $locationId = null,
        public ?PersonId $personId = null,
        public ?ImageId $imageId = null,
        public ?RecurrenceId $recurrenceId = null
    ) {
    }

	public function getStatus(): Status
	{
		return $this->status;
	}

	public function setStatus(Status $status): Event
	{
		return clone($this, [
			'status' => $status
		]);
	}

	public function acceptsBookings(): bool
	{
		if ($this->bookingPolicy === null) {
			return false;
		}

		return $this->bookingPolicy->enabled();
	}

	public function getAvailableTickets(DateTimeImmutable $now): ?TicketCollection
	{
		if ($this->tickets === null || $this->ticketBookingsMap === null) {
			return null;
		}

		return $this->tickets->getAvailableTickets($this->ticketBookingsMap, $now);
	}

	public function getLowestAvailablePrice(DateTimeImmutable $now): ?Price
	{
		$availableTickets = $this->getAvailableTickets($now);
		if ($availableTickets === null) {
			return null;
		}

		$lowestPrice = $availableTickets->getLowestAvailablePrice($now);
		return $lowestPrice;
	}

	public function getFreeSpaces(DateTimeImmutable $now): ?int
	{
		if ($this->tickets === null || $this->ticketBookingsMap === null) {
			return null;
		}
		$ticketFreeSpaces = $this->tickets->getFreeSpaces($this->ticketBookingsMap);
		$eventSoldTotal = $this->ticketBookingsMap->getTotalBookedCount(); 
		$eventFreeSpaces = ($this->overallCapacity === null) ? null : max(0, $this->overallCapacity - $eventSoldTotal);

		if ($ticketFreeSpaces === null && $eventFreeSpaces === null) {
			return null; 
		}

		if ($ticketFreeSpaces === null) return $eventFreeSpaces;
		if ($eventFreeSpaces === null) return $ticketFreeSpaces;

		return min($ticketFreeSpaces, $eventFreeSpaces);
	}

	public function canBookAt(DateTimeImmutable $now): BookingDecision
	{
		if( $this->tickets === null || $this->ticketBookingsMap === null) {
			return BookingDecision::deny(BookingDenyReason::NO_TICKETS);
		}
		if ($this->bookingPolicy === null) {
			return BookingDecision::deny(BookingDenyReason::DISABLED);
		}

		if(!$this->getFreeSpaces( $now)) {
			return BookingDecision::deny(BookingDenyReason::SOLD_OUT);
		}

		return $this->bookingPolicy->canBookAt($now);
	}
}
