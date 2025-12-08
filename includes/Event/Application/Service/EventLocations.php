<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Application\Service;

use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\EventCollection;
use Contexis\Events\Location\Application\LocationDto;
use Contexis\Events\Location\Application\LocationDtoCollection;
use Contexis\Events\Location\Domain\LocationRepository;
use Contexis\Events\Shared\Application\Contracts\Service;

final class EventLocations implements Service
{
    public function __construct(
        private LocationRepository $locations,
    ) {
    }

    public static function create(LocationRepository $locations): self
    {
        return new self($locations);
    }

    public function preloadDtos(EventCollection $events): ?LocationDtoCollection
    {
        $ids = array_map(function (Event $event) {
            return $event->locationId;
        }, $events->toArray())
          |> array_filter(...)
          |> array_unique(...);

        if ($ids === []) return null;
        $collection = $this->locations->findByIds($ids);

        return LocationDtoCollection::fromDomainCollection($collection);
    }
}
