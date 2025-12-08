<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Application;

use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\EventSpaces;

final class GetEventSpaces
{
    public function __construct(
        private EventRepository $events,
        private BookingRepository $bookings
    ) {
    }

    public function perform(int $eventId): EventSpaces
    {
        $now = new \DateTimeImmutable('now', $this->tz);

        $capacity = $this->events->getCapacity($eventId);
        $sum      = $this->bookings->summarizeSpaces($eventId, $now); // pendingActive drin
        $waiting  = $this->bookings->countWaitingList($eventId);

        $confirmed = $sum->confirmed;
        $pending   = $sum->pendingActive; // Policy: pending blockt immer

        return new EventSpaces(
            capacity:     $capacity,
            confirmed:    $confirmed,
            pending:      $pending,
            rejected:     $sum->rejected,
            waiting_list: $waiting,
            canceled:     $sum->canceled,
            expired:      $sum->expired,
            available:    max(0, $capacity - ($confirmed + $pending))
        );
    }
}
