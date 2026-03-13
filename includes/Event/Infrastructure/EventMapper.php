<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Booking\Infrastructure\WpBookingOptions;
use Contexis\Events\Event\Domain\ValueObjects\BookingPolicy;
use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Domain\ValueObjects\EventViewConfig;
use Contexis\Events\Event\Domain\ValueObjects\EventForms;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceId;
use Contexis\Events\Event\Domain\Ticket;
use Contexis\Events\Event\Domain\TicketCollection;
use Contexis\Events\Event\Domain\ValueObjects\EventCoupons;
use Contexis\Events\Event\Infrastructure\Mappers\EventPostStatusMapper;
use Contexis\Events\Form\Domain\FormId;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostStatusMapper;
use Contexis\Events\Location\Domain\LocationId;
use Contexis\Events\Media\Domain\ImageId;
use Contexis\Events\Person\Domain\PersonId;
use Contexis\Events\Shared\Domain\ValueObjects\Status;
use Contexis\Events\Shared\Domain\ValueObjects\AuthorId;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Infrastructure\Contracts\PostMapper;

use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;
use Contexis\Events\Shared\Presentation\Contracts\CriteriaMapper;
use DateTimeImmutable;

final class EventMapper implements PostMapper
{
    public static function map(PostSnapshot $post): Event
    {

        $timezone = $post->getString('_timezone') ?? wp_timezone();

        $event = new Event(
            id: EventId::from($post->id),
            status: EventPostStatusMapper::fromPost($post->post_status),
            name: $post->getString('post_title'),
            audience: $post->getString('audience') ?? null,
            description: $post->getString('post_excerpt'),
            authorId: new AuthorId($post->getInt('post_author')),
            eventViewConfig: EventViewConfig::fromArray($post->getArray(EventMeta::VIEW_CONFIG, [])),
            startDate: $post->getDateTime(EventMeta::EVENT_START, $timezone) ?: new DateTimeImmutable('01-01-1970 00:00:00', $timezone),
            endDate: $post->getDateTime(EventMeta::EVENT_END, $timezone) ?: new DateTimeImmutable('01-01-1970 00:00:00', $timezone),
            createdAt: $post->getDateTime('post_date', $timezone),
            locationId: LocationId::from($post->getInt(EventMeta::LOCATION_ID)),
            imageId: ImageId::from($post->getInt('_thumbnail_id')),
            recurrenceId: RecurrenceId::from($post->getInt(EventMeta::RECURRENCE_ID)),
            personId: $post->getInt('_person_id') ? PersonId::from($post->getInt(EventMeta::PERSON_ID)) : null
        );

		if(!$post->getBool(EventMeta::BOOKING_ENABLED)) {
			return $event;
		}

		$currency_string = $post->getString(EventMeta::BOOKING_CURRENCY) ?? get_option(WpBookingOptions::BOOKING_CURRENCY, 'USD');
		$currency = Currency::fromCode($currency_string);
		$booking_policy = $post->getBool('_booking_enabled') ? BookingPolicy::create(
            enabled: true,
            start: $post->getDateTime('_booking_start', $timezone),
            end: $post->getDateTime('_booking_end', $timezone),
            event_created_at: $post->getDateTime('post_date', $timezone),
            event_start: $post->getDateTime('_event_start', $timezone),
        ) : BookingPolicy::createWithDisabledBookings();

		$event = $event->withBookings(
			bookingPolicy: $booking_policy,
			tickets: self::ticketsFromArray($post->getArray(EventMeta::TICKETS, []), $currency),
			currency: $currency,
			forms: new EventForms(
                bookingForm: FormId::from($post->getInt(EventMeta::BOOKING_FORM)),
                attendeeForm: FormId::from($post->getInt(EventMeta::ATTENDEE_FORM))
            ),
			eventCoupons: new EventCoupons(
				enabled: $post->getBool(EventMeta::ALLOW_COUPONS, true) ?? true,
				allowedIds: array_map('intval', $post->getArray(EventMeta::COUPONS_ALLOWED, [])),
			),
			overallCapacity: $post->getInt(EventMeta::BOOKING_CAPACITY),
			donationEnabled: $post->getBool(EventMeta::DONATION_ENABLED, false) ?? false
		);
		
		
        return $event;
    }

    private static function ticketsFromArray(array $ticketsData, Currency $currency): TicketCollection
    {
        $tickets = [];
        foreach ($ticketsData as $ticketData) {
            $tickets[] = TicketMapper::fromArray($ticketData, $currency);
        }
        return TicketCollection::from(...$tickets);
    }
}
