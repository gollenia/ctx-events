<?php

namespace Contexis\Events\Infrastructure\Persistence\Mappers;

use Contexis\Events\Domain\Models\Event;
use Contexis\Events\Domain\ValueObjects\BookingPolicy;
use Contexis\Events\Domain\ValueObjects\EventSchedule;
use Contexis\Events\Domain\Collections\TicketCollection;
use Contexis\Events\Domain\ValueObjects\EventStatus;
use Contexis\Events\Domain\ValueObjects\EventViewConfig;
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

        $timezone = $post->getString('_timezone') ?? wp_timezone();

        $booking_policy = $post->getBool('_booking_enabled') ? BookingPolicy::create(
            enabled: true,
            start: $post->getDateTime('_booking_start', $timezone),
            end: $post->getDateTime('_booking_end', $timezone),
            event_created_at: $post->getDateTime('post_date', $timezone),
            event_start: $post->getDateTime('_event_start', $timezone),
        ) : BookingPolicy::createWithDisabledBookings();

        $event = new Event(
            id: EventId::from($post->id),
            name: $post->getString('post_title'),
            audience: $post->getString('audience') ?? null,
            description: $post->getString('post_excerpt'),
            eventStatus: EventStatus::from($post->getString('post_status')),
            authorId: new AuthorId($post->getInt('post_author')),
            bookingPolicy: $booking_policy,
            eventViewConfig: EventViewConfig::fromArray($post->getArray('_events_view_config', [])),
            startDate: $post->getDateTime('_event_start', $timezone),
            endDate: $post->getDateTime('_event_end', $timezone),
            createdAt: $post->getDateTime('post_date', $timezone),
            locationId: LocationId::from($post->getInt('_location_id')),
            imageId: ImageId::from($post->getInt('_thumbnail_id')),
            recurrenceId: RecurrenceId::from($post->getInt('_recurrence_id')),
        );

        return $event;
    }
}
