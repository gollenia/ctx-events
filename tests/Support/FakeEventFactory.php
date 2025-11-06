<?php

namespace Tests\Support;
use Contexis\Events\Domain\Models\Event;
use Contexis\Events\Domain\ValueObjects\BookingPolicy;
use Contexis\Events\Domain\ValueObjects\EventStatus;
use Contexis\Events\Domain\ValueObjects\Id\EventId;
use Contexis\Events\Domain\ValueObjects\Id\AuthorId;
use Contexis\Events\Domain\ValueObjects\Id\ImageId;
use Contexis\Events\Domain\ValueObjects\Id\LocationId;
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
			startDate: toImmutable(fake()->dateTimeBetween('now', '+1 month')),
			endDate: toImmutable(fake()->dateTimeBetween('+1 month', '+2 months')),
			createdAt: toImmutable(fake()->dateTimeBetween('-1 year', 'now')),
			booking_policy: BookingPolicy::create_from_values(
				enabled: true,
				start: toImmutable(fake()->dateTimeBetween('now', '+1 month')),
				end: toImmutable(fake()->dateTimeBetween('+1 month', '+2 months')),
				event_created_at: toImmutable(fake()->dateTimeBetween('-1 year', 'now')),
				event_start: toImmutable(fake()->dateTimeBetween('now', '+1 month'))
			),
			author_id: AuthorId::from(fake()->numberBetween(1, 1000)),
			location_id: LocationId::from(fake()->numberBetween(1, 1000)),
			attachment_id: ImageId::from(fake()->numberBetween(1, 1000)),
			recurrence_id: null
		);
	}
}