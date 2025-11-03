<?php

namespace Contexis\Events\Application\Services;

use Contexis\Events\Domain\Contracts\EventRepository;
use Contexis\Events\Domain\Contracts\BookingRepository;
use Contexis\Events\Domain\Policies\ExpirationPolicy;
use Contexis\Events\Domain\ValueObjects\EventSpaces;

final class GetEventSpaces
{
    public function __construct(
        private EventRepository $events,
        private BookingRepository $bookings
    ) {}

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