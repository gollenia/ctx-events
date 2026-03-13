<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Domain\ValueObjects;

enum BookingStatus: int
{
    case PENDING = 1;
    case APPROVED = 2;
    case CANCELED = 3;
    case EXPIRED = 4;
    case DELETED = 9;

    public function canTransitionTo(BookingStatus $newStatus): bool
    {
        return match ($this) {
            self::PENDING => in_array($newStatus, [self::APPROVED, self::CANCELED, self::EXPIRED, self::DELETED]),
            self::APPROVED => in_array($newStatus, [self::CANCELED, self::DELETED, self::PENDING]),
            self::CANCELED => in_array($newStatus, [self::PENDING, self::DELETED]),
            self::EXPIRED => in_array($newStatus, [self::PENDING, self::DELETED]),
            self::DELETED => false,
        };
    }
}
