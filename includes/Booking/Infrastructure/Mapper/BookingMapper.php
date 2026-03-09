<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Infrastructure\Mapper;

use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Payment\Domain\TransactionCollection;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Contexis\Events\Shared\Infrastructure\Contracts\DatabaseMapper;

final class BookingMapper implements DatabaseMapper
{
	public static function map(array $data): Booking
	{
		$registration = is_string($data['registration']) ? json_decode($data['registration'], true) : ($data['registration'] ?? []);

		return new Booking(
			reference: BookingReference::fromString($data['uuid']),
			email: Email::tryFrom($data['email']),
			name: PersonName::from($data['first_name'] ?? '', $data['last_name'] ?? ''),
			priceSummary: self::priceMapper($data),
			bookingTime: \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data['date'])
				?: new \DateTimeImmutable($data['date']),
			status: BookingStatus::from((int) $data['status']),
			registration: new RegistrationData($registration ?? []),
			attendees: AttendeeCollection::fromArray($data['attendees'] ?? []),
			gateway: $data['gateway'] ?? null,
			coupon: null,
			transactions: TransactionCollection::fromArray($data['transactions'] ?? []),
			eventId: EventId::from((int) $data['event_id']),
			id: BookingId::from((int) $data['id']),
		);
	}

	private static function priceMapper(array $data): PriceSummary
	{
		$currency = Currency::fromCode($data['currency']);
		return PriceSummary::fromValues(
			bookingPrice: Price::from((int) $data['booking_price'], $currency),
			donationAmount: Price::from((int) $data['donation_amount'], $currency),
			discountAmount: Price::from((int) $data['discount_amount'], $currency),
			currency: $currency
		);
	}
}
