<?php

namespace Contexis\Events\Domain\Models;

use Contexis\Events\Domain\ValueObjects\Email;
use Contexis\Events\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Domain\Models\Event;
use Contexis\Events\Domain\Collections\AttendeeCollection;
use Contexis\Events\Domain\Collections\LogEntryCollection;
use Contexis\Events\Domain\Collections\TransactionCollection;
use Contexis\Events\Domain\Collections\RecordCollection;
use Contexis\Events\Domain\Models\Coupon;
use Contexis\Events\Domain\ValueObjects\Id\BookingId;
use Contexis\Events\Domain\ValueObjects\Id\EventId;

/**
 * @package Contexis\Events\Domain\Models
 * @schema.booking
 */
final class Booking
{
    use \Contexis\Events\Core\Traits\ReplicatesProperties;

    public function __construct(
        public readonly BookingId $id,
        public readonly Email $userEmail,
        public readonly PriceSummary $price_summary,
        public readonly \DateTimeImmutable $bookingTime,
        public readonly BookingStatus $status,
        public readonly ?array $registration,
        public readonly AttendeeCollection $attendees,
        public readonly ?string $gateway,
        public readonly ?Coupon $coupon,
        public readonly ?TransactionCollection $transactions,
        public readonly EventId $eventId,
        public readonly ? $notes,
        public readonly ?LogEntryCollection $log
    ) {
    }

    public function withBookingStatus(BookingStatus $status): self
    {
        return $this->replicate(['status' => $status]);
    }
}
