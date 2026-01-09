<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Domain;

use Contexis\Events\Booking\Domain\ValueObjects\TicketSalesStats;
use Contexis\Events\Booking\Domain\ValueObjects\BookingId;

interface BookingRepository
{
    public function find(BookingId $id): ?Booking;
	public function getSalesStatsForEvent(int $eventId): TicketSalesStats;
}
