<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Domain;

use Contexis\Events\Booking\Domain\ValueObjects\BookingCode;
use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Booking\Domain\ValueObjects\BookingNotesCollection;
use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\LogEntry;
use Contexis\Events\Booking\Domain\ValueObjects\LogEntryCollection;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Payment\Domain\Coupon;
use Contexis\Events\Payment\Domain\TransactionCollection;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use DateTimeImmutable;

/**
 * @package Contexis\Events\Booking\Domain
 * @schema.booking
 */
final readonly class Booking
{
    public function __construct(
        public BookingReference $reference,
        public Email $email,
        public PersonName $name,
        public PriceSummary $priceSummary,
        public \DateTimeImmutable $bookingTime,
        public BookingStatus $status,
        public RegistrationData $registration,
        public AttendeeCollection $attendees,
        public ?string $gateway,
        public ?Coupon $coupon,
        public ?TransactionCollection $transactions,
        public EventId $eventId,
        public ?BookingId $id = null,
        public BookingNotesCollection $notes = new BookingNotesCollection([]),
        public LogEntryCollection $logEntries = new LogEntryCollection([]),
    ) {
    }

	public static function createPending(
		BookingReference $reference,
		Email $email,
		PersonName $name,
		DateTimeImmutable $bookingTime,
		EventId $eventId,
		RegistrationData $registration,
		AttendeeCollection $attendees,
		PriceSummary $priceSummary,
		string $gateway,
		?Coupon $coupon	= null
	): self {
		return new self(
			reference: $reference,
			email: $email,
			name: $name,
			priceSummary: $priceSummary,
			bookingTime: $bookingTime,
			status: BookingStatus::PENDING,
			registration: $registration,
			attendees: $attendees,
			gateway: $gateway,
			coupon: $coupon,
			transactions: null,
			eventId: $eventId,
		);
	}

	public function withId(BookingId $id): self
	{
		return clone($this, ['id' => $id]);
	}

	public function withPriceSummary(PriceSummary $priceSummary): self
	{
		return clone($this, ['priceSummary' => $priceSummary]);
	}

    public function appendLogEntry(LogEntry $entry): self
    {
        return clone($this, ['logEntries' => $this->logEntries->add($entry)]);
    }

    public function withBookingStatus(BookingStatus $status): self
    {
        return clone($this, ['status' => $status]);
    }

    public function withNotes(BookingNotesCollection $notes): self
    {
        return clone($this, ['notes' => $notes]);
    }

    public function withLogEntries(LogEntryCollection $logEntries): self
    {
        return clone($this, ['logEntries' => $logEntries]);
    }

	public function countAttendees(): int
	{
		return count($this->attendees);
	}

	public function update(
		RegistrationData $registration,
		AttendeeCollection $attendees,
		string $gateway,
		BookingNotesCollection $notes,
		PriceSummary $priceSummary,
	): self {
		return clone($this, [
			'email' => $registration->requireEmail(),
			'name' => $registration->requirePersonName(),
			'registration' => $registration,
			'attendees' => $attendees,
			'gateway' => $gateway,
			'notes' => $notes,
            'logEntries' => $this->logEntries,
			'priceSummary' => $priceSummary,
		]);
	}

}
