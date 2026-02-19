<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\Services;

use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class CancelBookingsForEvent
{
	public function __construct(
		private BookingRepository $bookingRepository,
		private EventDispatcher $eventDispatcher,
		private \Contexis\Events\Shared\Domain\Contracts\Clock $clock
	) {
	}

	public function execute(EventId $eventId): void
	{
		$bookings = $this->bookingRepository->findByEventId($eventId);
		foreach ($bookings as $booking) {
			$booking->cancelledAt = $this->clock->now();
			$this->bookingRepository->save($booking);
			$this->eventDispatcher->dispatch(new BookingCancelledByAdmin(
				$booking,
				$booking->email,
				'Booking cancelled by admin'
			));
		}
	}
}