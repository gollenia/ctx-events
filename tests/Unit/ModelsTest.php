<?php

namespace Tests\Unit;

use Contexis\Events\Domain\Models\Event;
use Contexis\Events\Domain\ValueObjects\BookingPolicy;
use Contexis\Events\Domain\ValueObjects\EventStatus;
use Contexis\Events\Domain\ValueObjects\Id\EventId;
use Contexis\Events\Domain\ValueObjects\Id\AuthorId;
use Contexis\Events\Domain\ValueObjects\Id\ImageId;
use Contexis\Events\Domain\ValueObjects\Id\LocationId;
use Contexis\Events\Domain\Models\Location;
use Tests\Support\DateHelpers;
use Tests\Support\FakeEventFactory;


use function Pest\Faker\fake;

test('can create event with valid data', function () {
	$event = new Event(
		id: EventId::from(1),
		name: "My new event",
		description: "This is a description of my new event.",
		audience: "public",
		eventStatus: EventStatus::Published,
		startDate: new \DateTimeImmutable('2024-07-01 10:00:00'),
		endDate: new \DateTimeImmutable('2024-08-01 10:00:00'),
		createdAt: new \DateTimeImmutable('2023-07-01 10:00:00'),
		bookingPolicy: BookingPolicy::create(
			enabled: true,
			start: new \DateTimeImmutable('2024-07-01 10:00:00'),
			end: new \DateTimeImmutable('2024-08-01 10:00:00'),
			event_created_at: new \DateTimeImmutable('2023-07-01 10:00:00'),
			event_start: new \DateTimeImmutable('2024-07-01 10:00:00')
		),
		eventViewConfig: new \Contexis\Events\Domain\ValueObjects\EventViewConfig(),
		authorId: AuthorId::from(3),
		locationId: LocationId::from(4),
		imageId: ImageId::from(5),
		recurrenceId: null
	);

	expect($event)->toBeInstanceOf(Event::class)
		->and($event->id)->toBeInstanceOf(EventId::class)
		->and($event->id->toInt())->toBe(1)
		->and($event->name)->toBe("My new event")
		->and($event->description)->toBe("This is a description of my new event.")
		->and($event->audience)->toBe("public")
		->and($event->startDate)->format('Y-m-d H:i:s')->toBe('2024-07-01 10:00:00')
		->and($event->endDate)->format('Y-m-d H:i:s')->toBe('2024-08-01 10:00:00')
		->and($event->createdAt)->format('Y-m-d H:i:s')->toBe('2023-07-01 10:00:00')
		->and($event->authorId->toInt())->toBe(3)
		->and($event->imageId->toInt())->toBe(5)
		->and($event->locationId->toInt())->toBe(4)
		->and($event->eventStatus)->toBe(EventStatus::Published)
		->and($event->bookingPolicy)->toBeInstanceOf(BookingPolicy::class)
		->and($event->recurrenceId)->toBeNull()
		->and($event->eventViewConfig)->toBeInstanceOf(\Contexis\Events\Domain\ValueObjects\EventViewConfig::class);
});	

test('can create location with valid data', function () {

	$address = new \Contexis\Events\Domain\ValueObjects\Address(
		streetAddress: "123 Main St",
		extendedAddress: "Suite 100",
		addressLocality: "Anytown",
		addressRegion: "Stateville",
		addressCountry: "Countryland",
		postalCode: "12345"
	);

	$location = new Location(
		id: LocationId::from(1),
		name: "Conference Hall",
		address: $address,
		geoCoordinates: null,
		imageId: ImageId::from(2),
		externalUrl: "https://example.com/location/1"
	);

	expect($location->id)->toBeInstanceOf(LocationId::class)
	   ->and($location->name)->toBe("Conference Hall")
	   ->and($location->address)->toBeInstanceOf(\Contexis\Events\Domain\ValueObjects\Address::class)
	   ->and($location->geoCoordinates)->toBeNull()
	   ->and($location->imageId)->toBeInstanceOf(ImageId::class)
	   ->and($location->externalUrl)->toBe("https://example.com/location/1");
});