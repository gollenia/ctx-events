<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Domain;

use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Shared\Domain\Abstract\Collection;

final readonly class BookingCollection extends Collection
{
	public static function from(Booking ...$items): self
	{
		return new self($items);
	}

	public function getById(BookingId $id): ?Booking
	{
		foreach ($this->items as $booking) {
			if ($booking->id->equals($id)) {
				return $booking;
			}
		}

		return null;
	}

	public function cancellable(): self
	{
		$bookings = array_filter(
			$this->items,
			static fn (Booking $booking): bool => $booking->status->canTransitionTo(BookingStatus::CANCELED),
		);

		return self::from(...array_values($bookings));
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function registrationEntries(): array
	{
		return array_map(
			static fn (Booking $booking): array => $booking->registration->all(),
			$this->items,
		);
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function attendeeMetadataEntries(): array
	{
		$metadata = [];

		foreach ($this->items as $booking) {
			foreach ($booking->attendees as $attendee) {
				$metadata[] = $attendee->metadata;
			}
		}

		return $metadata;
	}
}
