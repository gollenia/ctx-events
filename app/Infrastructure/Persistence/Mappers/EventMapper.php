<?php

namespace Contexis\Events\Infrastructure\Persistence\Mappers;

use Contexis\Events\Domain\Models\Event;
use Contexis\Events\Domain\ValueObjects\BookingPolicy;
use Contexis\Events\Domain\ValueObjects\EventSchedule;
use Contexis\Events\Domain\Collections\TicketCollection;
use Contexis\Events\Domain\ValueObjects\EventStatus;
use DateTimeImmutable;

final class EventMapper {

	public static function map(array $post): Event {

		$timezone = isset($post['meta']['_timezone'][0]) && $post['meta']['_timezone'][0] !== ''
            ? new \DateTimeZone($post['meta']['_timezone'][0])
            : wp_timezone();
		
		$schedule = new EventSchedule(
			start: new DateTimeImmutable($post['meta']['_event_start'][0], $timezone) ?? null,
			end: new DateTimeImmutable($post['meta']['_event_end'][0], $timezone) ?? null,
			all_day: $post['meta']['_event_all_day'][0] ?? false,
			timezone: $timezone
		);

		$booking_policy = BookingPolicy::create_from_values(
			enabled: (isset($post['meta']['_booking_enabled'][0]) ? (bool)$post['meta']['_booking_enabled'][0] : false),
			start: !empty($post['meta']['_booking_start'][0]) ? new DateTimeImmutable($post['meta']['_booking_start'][0], $timezone) : null,
			end: !empty($post['meta']['_booking_end'][0]) ? new DateTimeImmutable($post['meta']['_booking_end'][0], $timezone) : null,
			event_created_at: new DateTimeImmutable($post['post_date'], $timezone) ?? null,
			event_start: new DateTimeImmutable($post['meta']['_event_start'][0], $timezone) ?? null,
		);

		$tickets = array_map(function($ticket) {
			return TicketMapper::map($ticket);
		}, $post['meta']['_tickets'] ?? []);

		$tickets = new TicketCollection(
			...$tickets
		);

		$event = new Event(
			id: $post['ID'],
			title: $post['post_title'],
			description: $post['post_excerpt'],
			status: EventStatus::from($post['post_status']),
			author: $post['post_author'],
			booking_policy: $booking_policy,
			schedule: $schedule,
			person_id: $post['meta']['_person_id'][0] ?? null,
			location_id: $post['meta']['_location_id'][0] ?? null,
			created_at: new \DateTimeImmutable($post['post_date']),
			tickets: $tickets
		);
		// Weitere Felder und Metadaten können hier zugewiesen werden
		return $event;
	}
}