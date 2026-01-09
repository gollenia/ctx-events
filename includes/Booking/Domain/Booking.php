<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Domain;

use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Payment\Domain\Coupon;
use Contexis\Events\Payment\Domain\TransactionCollection;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\LogEntryCollection;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;

/**
 * @package Contexis\Events\Booking\Domain
 * @schema.booking
 */
final class Booking
{
    public function __construct(
        public readonly BookingId $id,
        public readonly Email $email,
        public readonly PriceSummary $priceSummary,
        public readonly \DateTimeImmutable $bookingTime,
        public readonly BookingStatus $status,
        public readonly RegistrationData $registration,
        public readonly AttendeeCollection $attendees,
        public readonly ?string $gateway,
        public readonly ?Coupon $coupon,
        public readonly ?TransactionCollection $transactions,
        public readonly EventId $eventId,
    ) {
    }

    public function withBookingStatus(BookingStatus $status): self
    {
        return clone($this, ['status' => $status]);
    }
}
