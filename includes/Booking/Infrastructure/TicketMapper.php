<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Infrastructure;

use Contexis\Events\Event\Domain\Ticket;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\Price;

final class TicketMapper
{
    public static function map(array $data): Ticket
    {


        return new Ticket(
            id: $data['id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            price: new Price($data['price'], Currency::fromCode(get_option(WpBookingOptions::BOOKING_CURRENCY, 'EUR'))),
            capacity: isset($data['capacity']) ? (int)$data['capacity'] : null,
            min: isset($data['min_per_booking']) ? (int)$data['min_per_booking'] : null,
            max: isset($data['max_per_booking']) ? (int)$data['max_per_booking'] : null,
            enabled: isset($data['enabled']) ? (bool)$data['enabled'] : null,
			order: isset($data['order']) ? (int)$data['order'] : null,
			form: $data['form'] ?? null,
            salesStart: isset($data['sales_start']) ? new \DateTimeImmutable($data['sales_start']) : null,
            salesEnd: isset($data['sales_end']) ? new \DateTimeImmutable($data['sales_end']) : null,
        );
    }
}
