<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Domain\ValueObjects;

use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Event\Domain\ValueObjects\TicketId;

final class TicketSalesCount
{
	public function __construct(
		public readonly TicketId $ticketId,
		private readonly int $pending,
		private readonly int $approved,
		private readonly int $canceled,
		private readonly int $expired
	) {
	}

	public static function fromArray(TicketId $ticketId, array $countsByStatus): self
    {
        return new self(
            $ticketId,
            $countsByStatus[BookingStatus::PENDING->value] ?? 0,
            $countsByStatus[BookingStatus::APPROVED->value] ?? 0,
            $countsByStatus[BookingStatus::CANCELED->value] ?? 0,
            $countsByStatus[BookingStatus::EXPIRED->value] ?? 0
        );
    }

	public static function empty(TicketId $ticketId): self
    {
        return new self($ticketId, 0, 0, 0, 0);
    }

	public function getBookedCount(): int
    {
        return $this->pending + $this->approved;
    }

	public function getLostCount(): int
    {
        return $this->canceled + $this->expired;
    }

	public function getCountFor(BookingStatus $status): int
    {
        return match ($status) {
            BookingStatus::PENDING => $this->pending,
            BookingStatus::APPROVED => $this->approved,
            BookingStatus::CANCELED => $this->canceled,
            BookingStatus::EXPIRED => $this->expired,
        };
    }
}