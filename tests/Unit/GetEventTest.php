<?php

use Contexis\Events\Application\DTO\Event;
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
use Tests\Support\FakeEventRepository;
use Tests\Support\FakeImageRepository;
use Tests\Support\FakeLocationRepository;
use Tests\Support\FakePersonRepository;

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

test('returns event dto when event found', function () {
    $event = FakeEventFactory::create();

    $eventRepository   = new FakeEventRepository($event);
    $peopleRepository   = new FakePersonRepository();
    $images   = new FakeImageRepository(null);
    $locations = new FakeLocationRepository(null);

    $uc = new GetEvent($eventRepository, $peopleRepository, $images, $locations);

    $dto = $uc->execute($event->id->toInt(), new EventIncludes(location: false, image: false), fakeViewContext());

    expect($dto)->toBeInstanceOf(Event::class);
    expect($dto->id)->toBe($event->id->toInt());
});
