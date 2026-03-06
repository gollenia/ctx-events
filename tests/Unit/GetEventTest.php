<?php
declare(strict_types=1);

use Contexis\Events\Event\Application\DTOs\EventIncludeRequest;
use Contexis\Events\Event\Application\DTOs\EventResponse;
use Contexis\Events\Event\Application\Service\EventPolicy;
use Contexis\Events\Event\Application\UseCases\GetEvent;
use Contexis\Events\Form\Domain\FormRepository;
use Contexis\Events\Location\Application\LocationDto;
use Contexis\Events\Shared\Application\ValueObjects\UserContext;
use Contexis\Events\Shared\Infrastructure\Wordpress\TaxonomyLoader;
use Tests\Support\FakeEventFactory;
use Tests\Support\FakeImageFactory;
use Tests\Support\FakeEventRepository;
use Tests\Support\FakeImageRepository;
use Tests\Support\FakeLocationFactory;
use Tests\Support\FakeLocationRepository;
use Tests\Support\FakePersonRepository;

use function Pest\Faker\fake;

function fakeUserContext(): UserContext
{
    return new UserContext(
        0,
        false,
        false,
        false
    );
}

// ======= TESTS =======

test('returns null when event not found', function () {
    $eventRepository = FakeEventRepository::empty();
    $peopleRepository = new FakePersonRepository();
    $imageRepository = new FakeImageRepository(null);
    $locationRepository = new FakeLocationRepository(null);
    $taxonomyLoader = new TaxonomyLoader();
    $formRepository = Mockery::mock(FormRepository::class);

    $eventPolicy = Mockery::mock(EventPolicy::class);

    $uc = new GetEvent(
        $eventRepository,
        $peopleRepository,
        $imageRepository,
        $locationRepository,
        $eventPolicy,
        $taxonomyLoader,
        $formRepository,
    );

    $dto = $uc->execute(999, new EventIncludeRequest(location: false, image: false), fakeUserContext());

    expect($dto)->toBeNull();
});

test('returns event dto without includes', function () {
    $id = fake()->numberBetween(1, 1000);
    $event = FakeEventFactory::create($id);

    $eventRepository = FakeEventRepository::one($event);
    $peopleRepository = new FakePersonRepository();
    $imageRepository = new FakeImageRepository(null);
    $locationRepository = new FakeLocationRepository(null);
    $taxonomyLoader = new TaxonomyLoader();
    $formRepository = Mockery::mock(FormRepository::class);

    $eventPolicy = Mockery::mock(EventPolicy::class);
    $eventPolicy->shouldReceive('userCanView')->andReturn(true);

    $uc = new GetEvent(
        $eventRepository,
        $peopleRepository,
        $imageRepository,
        $locationRepository,
        $eventPolicy,
        $taxonomyLoader,
        $formRepository,
    );

    $dto = $uc->execute($event->id->toInt(), new EventIncludeRequest(location: false, image: false), fakeUserContext());

    expect($dto)->toBeInstanceOf(EventResponse::class);
    expect($dto->id)->toBe($id);
    expect($dto->id)->toBe($event->id->toInt());
    expect($dto->locationDto)->toBeNull();
    expect($dto->imageDto)->toBeNull();
});


test('returns event dto when event found with includes', function () {
    $id = 5;
    $event = FakeEventFactory::create($id);
    $location = FakeLocationFactory::create();
    $image = FakeImageFactory::create();

    $eventRepository = FakeEventRepository::one($event);
    $peopleRepository = new FakePersonRepository();
    $imageRepository = new FakeImageRepository($image);
    $locationRepository = new FakeLocationRepository($location);
    $taxonomyLoader = new TaxonomyLoader();
    $formRepository = Mockery::mock(FormRepository::class);

    $eventPolicy = Mockery::mock(EventPolicy::class);
    $eventPolicy->shouldReceive('userCanView')->andReturn(true);

    $uc = new GetEvent(
        $eventRepository,
        $peopleRepository,
        $imageRepository,
        $locationRepository,
        $eventPolicy,
        $taxonomyLoader,
        $formRepository,
    );

    $dto = $uc->execute($event->id->toInt(), new EventIncludeRequest(location: true, image: true), fakeUserContext());

    expect($dto)->toBeInstanceOf(EventResponse::class);
    expect($dto->id)->toBe($id);
    expect($dto->locationDto)->toBeInstanceOf(LocationDto::class);
    expect($dto->imageDto)->not->toBeNull();
});
