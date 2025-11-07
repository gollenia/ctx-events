<?php

namespace Tests\Support;

use Contexis\Events\Domain\Models\Event;
use Contexis\Events\Domain\ValueObjects\BookingPolicy;
use Contexis\Events\Domain\ValueObjects\EventStatus;
use Contexis\Events\Domain\ValueObjects\Id\EventId;
use Contexis\Events\Domain\ValueObjects\Id\AuthorId;
use Contexis\Events\Domain\ValueObjects\Id\ImageId;
use Contexis\Events\Domain\ValueObjects\Id\LocationId;
use Tests\Support\DateHelpers;

use function Pest\Faker\fake;

class FakeEventFactory
{
    public static function create(): Event
    {

        return new Event(
            id: EventId::from(fake()->numberBetween(1, 1000)),
            name: fake()->sentence(3),
            description: fake()->paragraph(),
            audience: fake()->word(),
            eventStatus: EventStatus::Published,
            startDate: DateHelpers::toImmutable(fake()->dateTimeBetween('now', '+1 month')),
            endDate: DateHelpers::toImmutable(fake()->dateTimeBetween('+1 month', '+2 months')),
            createdAt: DateHelpers::toImmutable(fake()->dateTimeBetween('-1 year', 'now')),
            bookingPolicy: BookingPolicy::create(
                enabled: true,
                start: DateHelpers::toImmutable(fake()->dateTimeBetween('now', '+1 month')),
                end: DateHelpers::toImmutable(fake()->dateTimeBetween('+1 month', '+2 months')),
                event_created_at: DateHelpers::toImmutable(fake()->dateTimeBetween('-1 year', 'now')),
                event_start: DateHelpers::toImmutable(fake()->dateTimeBetween('now', '+1 month'))
            ),
            eventViewConfig: new \Contexis\Events\Domain\ValueObjects\EventViewConfig(),
            authorId: AuthorId::from(fake()->numberBetween(1, 1000)),
            locationId: LocationId::from(fake()->numberBetween(1, 1000)),
            imageId: ImageId::from(fake()->numberBetween(1, 1000)),
            recurrenceId: null
        );
    }
}
