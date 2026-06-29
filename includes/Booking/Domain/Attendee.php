<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Domain;

use Contexis\Events\Booking\Domain\Enums\AttendeeStatus;
use Contexis\Events\Booking\Domain\ValueObjects\AttendeeId;
use Contexis\Events\Event\Domain\ValueObjects\TicketId;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use DateTimeImmutable;

final readonly class Attendee
{
	/** @param array<string, mixed> $metadata */
    public function __construct(
        public TicketId $ticketId,
		public Price $ticketPrice,
		public ?PersonName $name,
        public ?DateTimeImmutable $birthDate = null,
        public array $metadata = [],
        public AttendeeStatus $status = AttendeeStatus::ACTIVE,
        public ?AttendeeId $id = null,
    ) {
    }

    public function isActive(): bool
    {
        return $this->status === AttendeeStatus::ACTIVE;
    }

    public function cancel(Price $cancellationPrice): self
    {
        return clone($this, [
            'ticketPrice' => $cancellationPrice,
            'status' => AttendeeStatus::CANCELLED,
        ]);
    }

	public function checkIn(): self
	{
		return clone($this, [
			'status' => AttendeeStatus::CHECKED_IN,
		]);
	}
}