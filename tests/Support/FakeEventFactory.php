<?php
declare(strict_types=1);

namespace Tests\Support;

use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\EventId;
use Contexis\Events\Booking\Domain\BookingPolicy;
use Contexis\Events\Shared\Domain\ValueObjects\Status;
use Contexis\Events\Shared\Domain\ValueObjects\AuthorId;
use Contexis\Events\Media\Domain\ImageId;
use Contexis\Events\Location\Domain\LocationId;
use Contexis\Events\Event\Domain\EventViewConfig;
use Tests\Support\DateHelpers;

use function Pest\Faker\fake;

class FakeEventFactory
{
    public static function create(int $id): Event
    {

        return new Event(
            id: EventId::from($id),
            status: Status::Published,
            name: fake()->sentence(3),
            description: fake()->paragraph(),
            audience: fake()->word(),
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
            eventViewConfig: new EventViewConfig(),
            authorId: AuthorId::from(fake()->numberBetween(1, 1000)),
            locationId: LocationId::from(fake()->numberBetween(1, 1000)),
            imageId: ImageId::from(fake()->numberBetween(1, 1000)),
            recurrenceId: null
        );
    }
}
