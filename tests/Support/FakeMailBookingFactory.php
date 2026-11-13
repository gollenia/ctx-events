<?php

declare(strict_types=1);

namespace Tests\Support;

use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use DateTimeImmutable;

final class FakeMailBookingFactory
{
    public static function create(EventId $eventId): Booking
    {
        return new Booking(
            reference: BookingReference::fromString('BOOK-MAIL-1001'),
            email: new Email('booking@example.test'),
            name: PersonName::from('Max', 'Mustermann'),
            priceSummary: PriceSummary::fromValues(
                bookingPrice: Price::from(5000, Currency::fromCode('EUR')),
                donationAmount: Price::from(0, Currency::fromCode('EUR')),
                discountAmount: Price::from(0, Currency::fromCode('EUR'))
            ),
            bookingTime: new DateTimeImmutable('2026-03-10 10:00:00'),
            status: BookingStatus::PENDING,
            registration: new RegistrationData([
                'email' => 'booking@example.test',
                'first_name' => 'Max',
                'last_name' => 'Mustermann',
            ]),
            attendees: AttendeeCollection::empty(),
            gateway: 'offline',
            coupon: null,
            transactions: null,
            eventId: $eventId,
        );
    }
}
