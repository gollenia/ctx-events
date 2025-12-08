<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Booking\Domain\BookingPolicy;
use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\EventId;
use Contexis\Events\Event\Domain\EventStatus;
use Contexis\Events\Event\Domain\EventViewConfig;
use Contexis\Events\Event\Domain\RecurrenceId;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostStatusMapper;
use Contexis\Events\Location\Domain\LocationId;
use Contexis\Events\Media\Domain\ImageId;
use Contexis\Events\Person\Domain\PersonId;
use Contexis\Events\Shared\Domain\ValueObjects\Status;
use Contexis\Events\Shared\Domain\ValueObjects\AuthorId;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;
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
            status: PostStatusMapper::fromPost($post->post_status),
            name: $post->getString('post_title'),
            audience: $post->getString('audience') ?? null,
            description: $post->getString('post_excerpt'),
            authorId: new AuthorId($post->getInt('post_author')),
            bookingPolicy: $booking_policy,
            eventViewConfig: EventViewConfig::fromArray($post->getArray('_events_view_config', [])),
            startDate: $post->getDateTime(EventMeta::EVENT_START, $timezone),
            endDate: $post->getDateTime(EventMeta::EVENT_END, $timezone),
            createdAt: $post->getDateTime('post_date', $timezone),
            locationId: LocationId::from($post->getInt(EventMeta::LOCATION_ID)),
            imageId: ImageId::from($post->getInt('_thumbnail_id')),
            recurrenceId: RecurrenceId::from($post->getInt(EventMeta::RECURRENCE_ID)),
            personId: $post->getInt('_person_id') ? PersonId::from($post->getInt(EventMeta::PERSON_ID)) : null,
        );

        return $event;
    }
}
