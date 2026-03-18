<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Domain\ValueObjects;

use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Communication\Domain\Enums\EmailTarget;
use Contexis\Events\Communication\Domain\Enums\EmailTrigger;
use Contexis\Events\Event\Domain\ValueObjects\EventId;

final readonly class EmailContext
{
    public function __construct(
        public EmailTrigger $trigger,
        public EventId $eventId,
        public EmailTarget $target,
        public ?string $gateway = null,
        public ?BookingStatus $bookingStatus = null,
        public bool $isFreeBooking = false,
    ) {
    }
}