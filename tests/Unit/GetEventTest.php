<?php

use Contexis\Events\Application\DTO\Event;
use Contexis\Events\Application\DTO\Location;
use Contexis\Events\Application\DTO\Image;
use Contexis\Events\Application\UseCases\GetEvent;
use Contexis\Events\Application\Query\EventIncludes;
use Contexis\Events\Application\Security\ViewContext;
use Contexis\Events\Domain\Repositories\EventRepository;
use Contexis\Events\Domain\Repositories\PersonRepository;
use Contexis\Events\Domain\Repositories\ImageRepository;
use Contexis\Events\Domain\Repositories\LocationRepository;
use Contexis\Events\Domain\ValueObjects\Id\EventId;
use Contexis\Events\Presentation\Security\ViewContextFactory;
use Tests\Support\FakeEventFactory;
use Tests\Support\FakeImageFactory;
use Tests\Support\FakeEventRepository;
use Tests\Support\FakeImageRepository;
use Tests\Support\FakeLocationFactory;
use Tests\Support\FakeLocationRepository;
use Tests\Support\FakePersonRepository;

use function Pest\Faker\fake;

function fakeViewContext(): ViewContext
{
    return new ViewContext(
        0,
        false,
        false,
        false
    );
}

// ======= TESTS =======

test('returns null when event not found', function () {
    $eventRepository   = new FakeEventRepository(null);
    $peopleRepository   = new FakePersonRepository();
    $images   = new FakeImageRepository(null);
    $locations = new FakeLocationRepository(null);

    $uc = new GetEvent($eventRepository, $peopleRepository, $images, $locations);

    $dto = $uc->execute(999, new EventIncludes(location: false, image: false), fakeViewContext());

    expect($dto)->toBeNull();
});

test('returns event dto without includes', function () {
    $id = fake()->numberBetween(1, 1000);
    $event = FakeEventFactory::create($id);

    $eventRepository   = new FakeEventRepository($event);
    $peopleRepository   = new FakePersonRepository();
    $images   = new FakeImageRepository(null);
    $locations = new FakeLocationRepository(null);

    $uc = new GetEvent($eventRepository, $peopleRepository, $images, $locations);

    $dto = $uc->execute($event->id->toInt(), new EventIncludes(location: false, image: false), fakeViewContext());

    expect($dto)->toBeInstanceOf(Event::class);
    expect($dto->id)->toBe($id);
    expect($dto->id)->toBe($event->id->toInt());
    expect($dto->includes->location)->toBeNull();
    expect($dto->includes->image)->toBeNull();
});


test('returns event dto when event found', function () {
    $id = 5;
    $event = FakeEventFactory::create($id);
    $location = FakeLocationFactory::create();
    $image = FakeImageFactory::create();

    $eventRepository   = new FakeEventRepository($event);
    $peopleRepository   = new FakePersonRepository();
    $images   = new FakeImageRepository($image);
    $locationRepository = new FakeLocationRepository($location);

    $uc = new GetEvent($eventRepository, $peopleRepository, $images, $locationRepository);

    $dto = $uc->execute($event->id->toInt(), new EventIncludes(location: true, image: true), fakeViewContext());

    expect($dto)->toBeInstanceOf(Event::class);
    expect($dto->id)->toBe($id);
    expect($dto->id)->toBe($event->id->toInt());
    expect($dto->includes->location)->toBeInstanceOf(Location::class);
    //expect($dto->includes->image)->toBeInstanceOf(Image::class);
});
