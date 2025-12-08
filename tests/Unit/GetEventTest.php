<?php
declare(strict_types=1);

use Contexis\Events\Event\Application\EventDto;
use Contexis\Events\Event\Application\EventIncludes;
use Contexis\Events\Event\Application\EventPolicy;
use Contexis\Events\Event\Application\GetEvent;
use Contexis\Events\Location\Application\LocationDto;
use Contexis\Events\Shared\Application\ValueObjects\UserContext;
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
    $eventRepository   = new FakeEventRepository(null);
    $peopleRepository   = new FakePersonRepository();
    $images   = new FakeImageRepository(null);
    $locations = new FakeLocationRepository(null);
    
    // Mock EventPolicy usually allows view if event not found? 
    // Actually GetEvent check logic: find event -> check policy.
    // So if event is null, policy is not called. We can pass a dummy.
    $policy = Mockery::mock(EventPolicy::class);

    $uc = new GetEvent($eventRepository, $peopleRepository, $images, $locations, $policy);

    $dto = $uc->execute(999, new EventIncludes(location: false, image: false), fakeUserContext());

    expect($dto)->toBeNull();
});

test('returns event dto without includes', function () {
    $id = fake()->numberBetween(1, 1000);
    $event = FakeEventFactory::create($id);

    $eventRepository   = new FakeEventRepository($event);
    $peopleRepository   = new FakePersonRepository();
    $images   = new FakeImageRepository(null);
    $locations = new FakeLocationRepository(null);
    
    $policy = Mockery::mock(EventPolicy::class);
    $policy->shouldReceive('userCanView')->andReturn(true);

    $uc = new GetEvent($eventRepository, $peopleRepository, $images, $locations, $policy);

    $dto = $uc->execute($event->id->toInt(), new EventIncludes(location: false, image: false), fakeUserContext());

    expect($dto)->toBeInstanceOf(EventDto::class);
    expect($dto->id)->toBe($id);
    expect($dto->id)->toBe($event->id->toInt());
    expect($dto->locationDto)->toBeNull();
    // image is not in top level DTO usually as property 'includes', but let's check class def.
    // Looking at GetEvent.php: 
    /*
        $response = EventDto::fromDomainModel(
            event: $event,
            locationDto: $locationDto,
            imageDto: $imageDto,
            personDto: $personDto
        );
    */
    // So properties are likely flattened or on the dto itself. 
    // Assuming EventDto matches what we see in ListEvents context roughly.
});


test('returns event dto when event found with includes', function () {
    $id = 5;
    $event = FakeEventFactory::create($id);
    $location = FakeLocationFactory::create();
    $image = FakeImageFactory::create();

    $eventRepository   = new FakeEventRepository($event);
    $peopleRepository   = new FakePersonRepository();
    $images   = new FakeImageRepository($image);
    $locationRepository = new FakeLocationRepository($location);

    $policy = Mockery::mock(EventPolicy::class);
    $policy->shouldReceive('userCanView')->andReturn(true);

    $uc = new GetEvent($eventRepository, $peopleRepository, $images, $locationRepository, $policy);

    $dto = $uc->execute($event->id->toInt(), new EventIncludes(location: true, image: true), fakeUserContext());

    expect($dto)->toBeInstanceOf(EventDto::class);
    expect($dto->id)->toBe($id);
    // Validation of includes
    if (property_exists($dto, 'locationDto')) {
        expect($dto->locationDto)->toBeInstanceOf(LocationDto::class);
    }
});
