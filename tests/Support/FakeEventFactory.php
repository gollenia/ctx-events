<?php
declare(strict_types=1);

namespace Tests\Support;

use Contexis\Events\Booking\Domain\ValueObjects\TicketBookingsMap;
use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\Enums\EventStatus;
use Contexis\Events\Event\Domain\Ticket;
use Contexis\Events\Event\Domain\TicketCollection;
use Contexis\Events\Event\Domain\ValueObjects\BookingPolicy;
use Contexis\Events\Event\Domain\ValueObjects\EventCoupons;
use Contexis\Events\Event\Domain\ValueObjects\EventForms;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Domain\ValueObjects\EventViewConfig;
use Contexis\Events\Event\Domain\ValueObjects\TicketId;
use Contexis\Events\Event\Infrastructure\EventMeta;
use Contexis\Events\Form\Domain\FormId;
use Contexis\Events\Location\Domain\LocationId;
use Contexis\Events\Media\Domain\ImageId;
use Contexis\Events\Person\Domain\PersonId;
use Contexis\Events\Shared\Domain\ValueObjects\AuthorId;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use DateTimeImmutable;

use function Pest\Faker\fake;

final class FakeEventFactory
{
    public static function create(int $id, array $metadataOverrides = []): Event
    {
        return self::createFromMetadata($id, $metadataOverrides);
    }

    public static function createFromMetadata(int $id, array $metadataOverrides = []): Event
    {
        $metadata = [
            ...self::defaultMetadata(),
            ...$metadataOverrides,
        ];

        $createdAt = self::toDateTime($metadata['post_date']);
        $startDate = self::toDateTime($metadata[EventMeta::EVENT_START]);
        $endDate = self::toDateTime($metadata[EventMeta::EVENT_END]);

        $eventId = EventId::from($id);
        $authorId = AuthorId::from((int) $metadata['post_author']);

        if ($eventId === null || $authorId === null) {
            throw new \RuntimeException('Invalid fake event base metadata.');
        }

        $event = new Event(
            id: $eventId,
            status: EventStatus::Published,
            name: fake()->sentence(3),
            startDate: $startDate,
            endDate: $endDate,
            createdAt: $createdAt,
            eventViewConfig: new EventViewConfig(),
            authorId: $authorId,
            description: fake()->paragraph(),
            audience: fake()->word(),
            locationId: LocationId::from((int) $metadata[EventMeta::LOCATION_ID]),
            personId: PersonId::from((int) $metadata[EventMeta::PERSON_ID]),
            imageId: ImageId::from((int) $metadata['_thumbnail_id']),
            recurrenceId: null
        );

        if (!((bool) $metadata[EventMeta::BOOKING_ENABLED])) {
            return $event;
        }

        $currencyCode = (string) $metadata[EventMeta::BOOKING_CURRENCY];
        $currency = Currency::fromCode($currencyCode);

        $event = $event->withBookings(
            bookingPolicy: BookingPolicy::create(
                enabled: true,
                start: self::toNullableDateTime($metadata[EventMeta::BOOKING_START]),
                end: self::toNullableDateTime($metadata[EventMeta::BOOKING_END]),
                event_created_at: $createdAt,
                event_start: $startDate,
            ),
            tickets: self::ticketsFromMeta((array) $metadata[EventMeta::TICKETS], $currency),
            ticketBookingsMap: TicketBookingsMap::fromArray((array) $metadata[EventMeta::CACHED_BOOKING_STATS]),
            currency: $currency,
            forms: new EventForms(
                bookingForm: FormId::from((int) $metadata[EventMeta::BOOKING_FORM]),
                attendeeForm: FormId::from((int) $metadata[EventMeta::ATTENDEE_FORM]),
            ),
            eventCoupons: new EventCoupons(
                enabled: (bool) $metadata[EventMeta::ALLOW_COUPONS],
            ),
            overallCapacity: (int) $metadata[EventMeta::BOOKING_CAPACITY],
        );

        return $event;
    }

    private static function defaultMetadata(): array
    {
        $startDate = DateHelpers::toImmutable(fake()->dateTimeBetween('now', '+1 month'));
        $endDate = DateHelpers::toImmutable(fake()->dateTimeBetween('+1 month', '+2 months'));
        $bookingStart = DateHelpers::toImmutable(fake()->dateTimeBetween('-1 month', 'now'));
        $bookingEnd = DateHelpers::toImmutable(fake()->dateTimeBetween('now', '+1 month'));
        $ticketId = 'ticket-main-' . fake()->numberBetween(1000, 9999);

        return [
            'post_author' => fake()->numberBetween(1, 200),
            'post_date' => fake()->dateTimeBetween('-1 year', '-2 months')->format('Y-m-d H:i:s'),
            '_thumbnail_id' => fake()->numberBetween(1, 1000),
            EventMeta::PERSON_ID => fake()->numberBetween(1, 1000),
            EventMeta::LOCATION_ID => fake()->numberBetween(1, 1000),
            EventMeta::EVENT_START => $startDate->format('Y-m-d H:i:s'),
            EventMeta::EVENT_END => $endDate->format('Y-m-d H:i:s'),
            EventMeta::BOOKING_ENABLED => true,
            EventMeta::BOOKING_START => $bookingStart->format('Y-m-d H:i:s'),
            EventMeta::BOOKING_END => $bookingEnd->format('Y-m-d H:i:s'),
            EventMeta::BOOKING_CURRENCY => 'EUR',
            EventMeta::BOOKING_CAPACITY => 200,
            EventMeta::BOOKING_FORM => fake()->numberBetween(1, 50),
            EventMeta::ATTENDEE_FORM => fake()->numberBetween(51, 100),
            EventMeta::ALLOW_COUPONS => true,
            EventMeta::TICKETS => [
                [
                    'ticket_id' => $ticketId,
                    'ticket_name' => 'Standard',
                    'ticket_description' => 'Standard ticket',
                    'ticket_price' => 1500,
                    'ticket_spaces' => 120,
                    'ticket_max' => 10,
                    'ticket_min' => 1,
                    'ticket_enabled' => true,
                    'ticket_start' => $bookingStart->format('Y-m-d H:i:s'),
                    'ticket_end' => $bookingEnd->format('Y-m-d H:i:s'),
                    'ticket_order' => 1,
                    'ticket_form' => fake()->numberBetween(1, 50),
                ],
            ],
            EventMeta::CACHED_BOOKING_STATS => [
                $ticketId => [
                    'pending' => 2,
                    'approved' => 4,
                    'canceled' => 1,
                    'expired' => 0,
                ],
            ],
        ];
    }

    private static function ticketsFromMeta(array $ticketsMeta, Currency $currency): TicketCollection
    {
        $tickets = [];

        foreach ($ticketsMeta as $ticketMeta) {
            $ticketId = TicketId::from((string) ($ticketMeta['ticket_id'] ?? ''));

            if ($ticketId === null) {
                continue;
            }

            $tickets[] = new Ticket(
                id: $ticketId,
                name: (string) ($ticketMeta['ticket_name'] ?? 'Ticket'),
                description: (string) ($ticketMeta['ticket_description'] ?? ''),
                price: Price::from((int) ($ticketMeta['ticket_price'] ?? 0), $currency),
                capacity: isset($ticketMeta['ticket_spaces']) ? (int) $ticketMeta['ticket_spaces'] : null,
                enabled: (bool) ($ticketMeta['ticket_enabled'] ?? true),
                salesStart: self::toNullableDateTime($ticketMeta['ticket_start'] ?? null),
                salesEnd: self::toNullableDateTime($ticketMeta['ticket_end'] ?? null),
                order: (int) ($ticketMeta['ticket_order'] ?? 0),
                form: (int) ($ticketMeta['ticket_form'] ?? 0),
                min: (int) ($ticketMeta['ticket_min'] ?? 1),
                max: (int) ($ticketMeta['ticket_max'] ?? 1),
            );
        }

        return TicketCollection::fromArray($tickets);
    }

    private static function toDateTime(mixed $value): DateTimeImmutable
    {
        return match (true) {
            $value instanceof DateTimeImmutable => $value,
            is_string($value) => new DateTimeImmutable($value),
            default => throw new \RuntimeException('Cannot convert metadata value to DateTimeImmutable.'),
        };
    }

    private static function toNullableDateTime(mixed $value): ?DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::toDateTime($value);
    }
}
