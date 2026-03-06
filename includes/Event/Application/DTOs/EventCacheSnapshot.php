<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Application\DTOs;

use Contexis\Events\Booking\Domain\ValueObjects\TicketBookingsMap;

final readonly class EventCacheSnapshot
{
   
    public function __construct(
        public int $eventId,
        public ?int $minPriceAmountCents,
        public ?int $maxPriceAmountCents,
        public int $availableSpaces,
        public TicketBookingsMap $bookingStats,
    ) {
    }
}
