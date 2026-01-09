<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Infrastructure\Mapper;

use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Payment\Domain\TransactionCollection;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\LogEntryCollection;
use Contexis\Events\Shared\Infrastructure\Contracts\DatabaseMapper;

final class BookingMapper implements DatabaseMapper
{
	public static function map(array $data): Booking
	{
		return new Booking(
			id: BookingId::from($data['id']),
			email: Email::tryFrom($data['email']),
			priceSummary: PriceSummary::fromValues(
				(int)$data['total_price'],
				(int)$data['donation_amount'],
				(int)$data['discount_amount'],
				$data['currency']
			),
			bookingTime: \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data['booking_time']),
			status: BookingStatus::from($data['status']),
			registration: $data['registration'],
			attendees: AttendeeCollection::fromArray($data['attendees']),
			gateway: $data['gateway'],
			coupon: $data['coupon'],
			transactions: TransactionCollection::fromArray($data['transactions']),
			eventId: EventId::from($data['event_id'])
		);
	}

}