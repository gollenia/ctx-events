<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Infrastructure\Mapper;

use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\ValueObjects\BookingNote;
use Contexis\Events\Booking\Domain\ValueObjects\BookingNotesCollection;
use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Booking\Domain\ValueObjects\LogEntry;
use Contexis\Events\Booking\Domain\ValueObjects\LogEntryCollection;
use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Payment\Domain\TransactionCollection;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Contexis\Events\Shared\Infrastructure\Contracts\DatabaseMapper;

final class BookingMapper implements DatabaseMapper
{
	public static function map(array $data): Booking
	{
		$registration = self::getRegistrationData($data);

		$rawNotes = is_string($data['notes'] ?? null)
			? json_decode($data['notes'], true)
			: ($data['notes'] ?? []);
		$notes = new BookingNotesCollection(...array_map(
			static fn(array $item): BookingNote => BookingNote::fromArray($item),
			$rawNotes ?? [],
		));
        $rawLogEntries = is_string($data['log'] ?? null)
            ? json_decode($data['log'], true)
            : ($data['log'] ?? []);
        $logEntries = new LogEntryCollection(...array_map(
            static fn(array $item): LogEntry => LogEntry::fromArray($item),
            $rawLogEntries ?? [],
        ));

		return new Booking(
			reference: BookingReference::fromString($data['uuid']),
			email: $registration->requireEmail(),
			name: $registration->requirePersonName(),
			priceSummary: $data['price_summary'] ? PriceSummary::fromArray(json_decode($data['price_summary'], true)) : null,
			bookingTime: \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data['date'])
				?: new \DateTimeImmutable($data['date']),
			status: BookingStatus::from((int) $data['status']),
			registration: $registration,
			attendees: AttendeeCollection::fromArray($data['attendees'] ?? []),
			gateway: $data['gateway'] ?? null,
			coupon: null,
			transactions: TransactionCollection::fromArray($data['transactions'] ?? []),
			eventId: EventId::from((int) $data['event_id']),
			id: BookingId::from((int) $data['id']),
			notes: $notes,
            logEntries: $logEntries,
		);
	}


	private static function getRegistrationData(array $data): RegistrationData
	{
		$registration = is_string($data['registration']) ? json_decode($data['registration'], true) : ($data['registration'] ?? []);
		return new RegistrationData($registration ?? []);
	}


}
