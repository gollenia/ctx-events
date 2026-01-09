<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Application\Service;

use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\EventCollection;
use Contexis\Events\Person\Application\PersonDto;
use Contexis\Events\Person\Application\PersonDtoCollection;
use Contexis\Events\Person\Domain\PersonRepository;

final class EventPersons
{
    public function __construct(
        private readonly PersonRepository $persons
    ) {
    }

    public static function create(PersonRepository $persons): self
    {
        return new self($persons);
    }

    public function preloadDtos(EventCollection $events): ?PersonDtoCollection
    {
        $ids = array_map(function (Event $event) {
            return $event->personId;
        }, $events->toArray())
          |> array_filter(...)
          |> array_unique(...);

        if ($ids === []) return null;

        $collection = $this->persons->findByIds($ids);

        return PersonDtoCollection::fromDomainCollection($collection);
    }
}
