<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Domain;

use Contexis\Events\Event\Domain\EventId;
use Contexis\Events\Payment\Domain\Coupon;
use Contexis\Events\Payment\Domain\TransactionCollection;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\PriceSummary;

/**
 * @package Contexis\Events\Booking\Domain
 * @schema.booking
 */
final class Booking
{
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
        //public readonly ?Note $notes,
        //public readonly ?LogEntryCollection $log
    ) {
    }

    public function withBookingStatus(BookingStatus $status): self
    {
        return clone($this, ['status' => $status]);
    }
}
