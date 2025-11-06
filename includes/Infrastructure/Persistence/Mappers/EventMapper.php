<?php

namespace Contexis\Events\Infrastructure\Persistence\Mappers;

use Contexis\Events\Domain\Models\Event;
use Contexis\Events\Domain\ValueObjects\BookingPolicy;
use Contexis\Events\Domain\ValueObjects\EventSchedule;
use Contexis\Events\Domain\Collections\TicketCollection;
use Contexis\Events\Domain\ValueObjects\EventStatus;
use Contexis\Events\Domain\ValueObjects\Id\AuthorId;
use Contexis\Events\Domain\ValueObjects\Id\EventId;
use Contexis\Events\Domain\ValueObjects\Id\LocationId;
use Contexis\Events\Domain\ValueObjects\Id\ImageId;
use Contexis\Events\Domain\ValueObjects\Id\RecurrenceId;
use Contexis\Events\Infrastructure\PostTypes\PostSnapshot;
use DateTimeImmutable;

final class EventMapper
{
    public static function map(PostSnapshot $post): Event
    {

        $timezone = $post->getMetaValue('_timezone') ?? wp_timezone();

        $booking_policy = BookingPolicy::create_from_values(
            enabled: (bool)$post->getMetaValue('_booking_enabled'),
            start: !empty($post->getMetaValue('_booking_start'))
            ? new DateTimeImmutable($post->getMetaValue('_booking_start'), $timezone)
            : null,
            end: !empty($post->getMetaValue('_booking_end')) ? new DateTimeImmutable($post->getMetaValue('_booking_end'), $timezone) : null,
            event_created_at: new DateTimeImmutable($post->post_date, $timezone) ?? null,
            event_start: new DateTimeImmutable($post->getMetaValue('_event_start'), $timezone) ?? null,
        );

        $event = new Event(
            id: EventId::from($post->id),
            name: $post->post_title,
            audience: $post->getMetaValue('audience') ?? null,
            description: $post->post_excerpt,
            eventStatus: EventStatus::from($post->post_status),
            author_id: new AuthorId($post->post_author),
            booking_policy: $booking_policy,
            startDate: new \DateTimeImmutable($post->getMetaValue('_event_start'), $timezone),
            endDate: new \DateTimeImmutable($post->getMetaValue('_event_end'), $timezone),
            createdAt: new \DateTimeImmutable($post->post_date),
            location_id: LocationId::from($post->getMetaValue('_location_id')),
            attachment_id: ImageId::from($post->getMetaValue('_thumbnail_id')),
            recurrence_id: RecurrenceId::from($post->getMetaValue('_recurrence_id')),
        );

        return $event;
    }
}
