<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\Services;

use Contexis\Events\Booking\Domain\Enums\BookingLogEvent;
use Contexis\Events\Booking\Application\Services\CancelOpenBookingTransactions;
use Contexis\Events\Booking\Domain\Enums\BookingLogLevel;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\LogEntry;
use Contexis\Events\Communication\Application\BookingEmailWarnings;
use Contexis\Events\Communication\Application\Contracts\BookingEmailTrigger;
use Contexis\Events\Communication\Domain\Enums\EmailTrigger;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\Contracts\CurrentActorProvider;

final class CancelBookingsForEvent
{
	public function __construct(
		private BookingRepository $bookingRepository,
		private Clock $clock,
		private CurrentActorProvider $currentActorProvider,
		private BookingEmailTrigger $bookingEmailTrigger,
		private ?CancelOpenBookingTransactions $openTransactionCanceler = null,
	) {
	}

	public function execute(
		EventId $eventId,
		bool $sendMail = true,
		?string $cancellationReason = null
	): void
	{
		foreach ($this->bookingRepository->findByEventId($eventId)->cancellable() as $booking) {
			$bookingId = $booking->id;
			if ($bookingId === null) {
				continue;
			}

			$updatedBooking = $booking
				->withBookingStatus(BookingStatus::CANCELED)
				->appendLogEntry(new LogEntry(
					eventType: BookingLogEvent::Cancelled,
					level: BookingLogLevel::Error,
					actor: $this->currentActorProvider->current(),
					timestamp: $this->clock->now(),
				));

			$this->bookingRepository->updateStatus(
				$bookingId,
				BookingStatus::CANCELED,
				$updatedBooking->logEntries
			);
			$this->openTransactionCanceler?->execute($booking);

			if (!$sendMail) {
				continue;
			}

			$emailResult = $this->bookingEmailTrigger->trigger(
				EmailTrigger::BOOKING_CANCELLED,
				$bookingId,
				$cancellationReason
			);
			$logEntries = BookingEmailWarnings::appendToLogEntries(
				$updatedBooking->logEntries,
				$emailResult,
				$this->clock->now(),
			);

			if ($logEntries === $updatedBooking->logEntries) {
				continue;
			}

			$this->bookingRepository->updateStatus($bookingId, BookingStatus::CANCELED, $logEntries);
		}
	}
}
