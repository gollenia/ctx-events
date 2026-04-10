<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application\DTOs;

use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Payment\Domain\TransactionCollection;

final readonly class TriggeredEmailContext
{
    public function __construct(
        public Booking $booking,
        public Event $event,
        public AttendeeCollection $attendees,
        public TransactionCollection $transactions,
        public ?string $cancellationReason = null,
    ) {
    }
}
