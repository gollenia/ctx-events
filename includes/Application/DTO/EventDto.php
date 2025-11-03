<?php

namespace Contexis\Events\Application\DTO;

use Contexis\Events\Domain\Collections\TicketCollection;
use Contexis\Events\Domain\Models\Location;
use Contexis\Events\Domain\ValueObjects\BookingPolicy;
use Contexis\Events\Domain\ValueObjects\EventSchedule;
use Contexis\Events\Domain\ValueObjects\EventStatus;

class EventDto {
	public function __construct(
		public readonly int $id,
		public readonly string $title,
		public readonly int $author,
		public readonly ?string $description,
		public readonly EventStatus $status,
		public readonly EventSchedule $schedule,
		public readonly BookingPolicy $booking_policy,
		public readonly ?TicketCollection $tickets = null,
		public readonly ?LocationDto $location = null,
		public readonly ?ImageDto $image = null,
		

	) {}
}
