<?php

namespace Contexis\Events\Infrastructure\Persistence\Mappers;

use Contexis\Events\Domain\Models\Ticket;
use Contexis\Events\Domain\ValueObjects\Price;

final class TicketMapper {

	public static function map(array $data): Ticket {
		return new Ticket(
			id: $data['id'],
			name: $data['name'],
			description: $data['description'] ?? null,
			price: new Price($data['price'], get_option('ctx_events_currency', 'USD')),
			capacity: isset($data['capacity']) ? (int)$data['capacity'] : null,
			min_per_booking: isset($data['min_per_booking']) ? (int)$data['min_per_booking'] : null,
			max_per_booking: isset($data['max_per_booking']) ? (int)$data['max_per_booking'] : null,
			enabled: isset($data['enabled']) ? (bool)$data['enabled'] : null,
			sales_start: isset($data['sales_start']) ? new \DateTimeImmutable($data['sales_start']) : null,
			sales_end: isset($data['sales_end']) ? new \DateTimeImmutable($data['sales_end']) : null,
		);
	}
}