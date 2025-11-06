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
use Tests\Support\FakeEventRepository;
use Tests\Support\FakeImageRepository;
use Tests\Support\FakeLocationRepository;
use Tests\Support\FakePersonRepository;

// ✏️ PASSE DIESE BUILDER an deine echten Konstruktoren an.
function makeEvent(int $id = 165, int $locationId = 7, int $attachmentId = 11): object
{
    return new Event(
        id: $id,
        location_id: $locationId,
        attachment_id: $attachmentId,
    );
}

function makeLocation(int $id = 7, string $name = 'Walchsee'): object
{
    $l = new stdClass();
    $l->id = $id;
    $l->name = $name;
    return $l;
}

function makeImage(int $id = 11, string $url = 'https://example/image.jpg'): object
{
    $img = new stdClass();
    $img->id = $id;
    $img->url = $url;
    return $img;
}

// ✏️ Falls ViewContext ctor Parameter braucht, hier anpassen.
function fakeViewContext(): ViewContext
{
    // z.B. return new ViewContext(ViewContext::PUBLIC);
    return new class () extends ViewContext {
    };
}

// ======= TESTS =======

test('returns null when event not found', function () {
    $events   = new FakeEventRepository(null);
    $people   = new FakePersonRepository();
    $images   = new FakeImageRepository(null);
    $locations = new FakeLocationRepository(null);

    $uc = new GetEvent($events, $people, $images, $locations);

    $dto = $uc->execute(999, new EventIncludes(location: false, image: false), fakeViewContext());

    expect($dto)->toBeNull();
});

test('does not call location/image repos when includes are false', function () {
    $event    = makeEvent();
    $events   = new FakeEventRepository(null);
    $people   = new FakePersonRepository();
    $images   = new FakeImageRepository(null);
    $locations = new FakeLocationRepository(null);

    $uc = new GetEvent($events, $people, $images, $locations);

    $dto = $uc->execute($event->id, new EventIncludes(location: false, image: false), fakeViewContext());

    // Repos wurden nicht angerührt:
    expect($locations->called)->toBeFalse()
        ->and($images->called)->toBeFalse();

    // Der DTO sollte existieren (Event gefunden), aber ohne Includes:
    // ✏️ Passe die folgenden Assertions an deine DTO-Struktur an.
    expect($dto)->not->toBeNull();
    // z.B.: expect($dto->includes->location)->toBeNull()->and($dto->includes->image)->toBeNull();
});

test('resolves location and image when includes are true', function () {
    $event    = makeEvent();
    $events   = new SpyEventRepo($event);
    $people   = new DummyPersonRepo();
    $imgObj   = makeImage();
    $locObj   = makeLocation();
    $images   = new SpyImageRepo($imgObj);
    $locations = new SpyLocationRepo($locObj);

    $uc = new GetEvent($events, $people, $images, $locations);

    $dto = $uc->execute($event->id, new EventIncludes(location: true, image: true), fakeViewContext());

    expect($locations->called)->toBeTrue()
        ->and($images->called)->toBeTrue();

    // ✏️ DTO-Assertions (anpassen an deine echten DTO-Properties)
    // expect($dto->includes->location->name)->toBe('Walchsee');
    // expect($dto->includes->image->url)->toBe('https://example/image.jpg');
});

test('passes EventId to repository', function () {
    $event    = makeEvent(165);
    $events   = new SpyEventRepo($event);
    $people   = new DummyPersonRepo();
    $images   = new SpyImageRepo(null);
    $locations = new SpyLocationRepo(null);

    $uc = new GetEvent($events, $people, $images, $locations);

    $uc->execute(165, new EventIncludes(location: false, image: false), fakeViewContext());

    // Wir erwarten, dass ein EventId-VO übergeben wurde:
    expect($events->lastFindArg)
        ->toBeInstanceOf(EventId::class)
        ->and((string)$events->lastFindArg->value ?? null)->toBe('165'); // ✏️ ggf. anpassen, je nach EventId-API
});
