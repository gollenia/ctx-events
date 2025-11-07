<?php

namespace Contexis\Events\Domain\Factories;

use Contexis\Events\Domain\Models\Event;
use Contexis\Events\Domain\ValueObjects\BookingPolicy;
use Contexis\Events\Domain\ValueObjects\Id\EventId;

final class EventFactory
{
    public static function withoutBooking(
		EventId $id,

	): Event
	{
		return new Event(
			id: $id, 
			name: $name, 
			description: $description, 
			audience: $audience, 
			eventStatus: $eventStatus, $startDate, $endDate, $createdAt, BookingPolicy::disabled(), 
		);
	}
   
}
