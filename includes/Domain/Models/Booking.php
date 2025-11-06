<?php

namespace Contexis\Events\Domain\Models;

use Contexis\Events\Domain\ValueObjects\Email;
use Contexis\Events\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Domain\Models\Event;
use Contexis\Events\Domain\Collections\AttendeeCollection;
use Contexis\Events\Domain\Collections\TransactionCollection;
use Contexis\Events\Domain\Collections\RecordCollection;
use Contexis\Events\Domain\Models\Coupon;
use Contexis\Events\Domain\ValueObjects\Id\BookingId;

/**
 * @package Contexis\Events\Domain\Models
 * @schema.booking
 */
final class Booking
{
    public function __construct(
        public readonly BookingId $id,
        public readonly Event $event,
        public readonly Email $user_email,
        public readonly PriceSummary $price_summary,
        public readonly \DateTimeImmutable $created_at,
        public readonly BookingStatus $status,
        public readonly ?array $registration,
        public readonly AttendeeCollection $attendees,
        public readonly ?string $gateway,
        public readonly ?Coupon $coupon,
        public readonly ?TransactionCollection $transactions,
        public readonly ?RecordCollection $notes,
        public readonly ?RecordCollection $log
    ) {
    }
}
