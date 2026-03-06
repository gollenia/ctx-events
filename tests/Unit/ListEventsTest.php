<?php
declare(strict_types=1);

use Contexis\Events\Event\Application\DTOs\EventCriteria;
use Contexis\Events\Event\Application\DTOs\EventIncludeRequest;
use Contexis\Events\Event\Application\DTOs\EventResponseCollection;
use Contexis\Events\Event\Application\Service\EventImages;
use Contexis\Events\Event\Application\Service\EventLocations;
use Contexis\Events\Event\Application\Service\EventPersons;
use Contexis\Events\Event\Application\Service\EventResponseAssembler;
use Contexis\Events\Event\Application\Service\EventTickets;
use Contexis\Events\Event\Application\UseCases\ListEvents;
use Contexis\Events\Event\Domain\EventStatusRepository;
use Contexis\Events\Event\Domain\ValueObjects\EventStatusCounts;
use Contexis\Events\Shared\Application\ValueObjects\UserContext;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Infrastructure\ValueObjects\OrderBy;
use Contexis\Events\Shared\Infrastructure\Wordpress\TaxonomyLoader;
use Tests\Support\FakeEventFactory;
use Tests\Support\FakeEventRepository;
use Tests\Support\FakeImageRepository;
use Tests\Support\FakeLocationRepository;
use Tests\Support\FakePersonRepository;

test('lists events with pagination and status counts', function () {
    $eventA = FakeEventFactory::create(101);
    $eventB = FakeEventFactory::create(202);

    $eventRepository = FakeEventRepository::many($eventA, $eventB);

    $statusCounts = new EventStatusCounts(
        publish: 2,
        future: 0,
        draft: 1,
        private: 0,
        pending: 0,
        trash: 0,
        cancelled: 1,
    );

    $statusRepository = Mockery::mock(EventStatusRepository::class);
    $statusRepository->shouldReceive('getCountsByStatus')
        ->once()
        ->andReturn($statusCounts);

    $clock = Mockery::mock(Clock::class);
    $clock->shouldReceive('now')->andReturn(new DateTimeImmutable('2026-03-04 10:00:00'));

    $assembler = new EventResponseAssembler(
        locations: EventLocations::create(new FakeLocationRepository()),
        images: EventImages::create(new FakeImageRepository()),
        persons: EventPersons::create(new FakePersonRepository()),
        tickets: EventTickets::onlyBookable(),
        taxonomyLoader: new TaxonomyLoader(),
        clock: $clock,
    );

    $useCase = new ListEvents(
        eventRepository: $eventRepository,
        eventStatusRepository: $statusRepository,
        eventResponseAssembler: $assembler,
        userContext: new UserContext(0, false, false, false),
    );

    $criteria = new EventCriteria(
        page: 2,
        perPage: 5,
        status: null,
        orderBy: OrderBy::default(),
    );

    $response = $useCase->execute($criteria, new EventIncludeRequest());

    expect($response)->toBeInstanceOf(EventResponseCollection::class);
    expect($response->count())->toBe(2);
    expect($response->pagination)->not->toBeNull();
    expect($response->pagination->currentPage)->toBe(2);
    expect($response->pagination->perPage)->toBe(5);
    expect($response->pagination->totalItems)->toBe(2);
    expect($response->statusCounts)->toBe($statusCounts);
    expect($response->statusCounts->publish)->toBe(2);
    expect($response->statusCounts->cancelled)->toBe(1);
});
