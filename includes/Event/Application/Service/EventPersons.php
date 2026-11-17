<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Application\Service;

use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\EventCollection;
use Contexis\Events\Media\Application\ImageDtoCollection;
use Contexis\Events\Media\Domain\ImageRepository;
use Contexis\Events\Person\Application\PersonDto;
use Contexis\Events\Person\Application\PersonDtoCollection;
use Contexis\Events\Person\Domain\PersonRepository;

final class EventPersons
{
    public function __construct(
        private readonly PersonRepository $persons,
        private readonly ImageRepository $images,
    ) {
    }

    public static function create(PersonRepository $persons, ImageRepository $images): self
    {
        return new self($persons, $images);
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
		$imageIds = [];

		foreach ($collection as $person) {
			if ($person->imageId !== null) {
				$imageIds[$person->imageId->toInt()] = $person->imageId;
			}
		}

		$images = $imageIds === []
			? null
			: ImageDtoCollection::fromDomainCollection(
				$this->images->findByIds(array_values($imageIds)),
			);
		$items = [];

		foreach ($collection as $person) {
			$items[] = PersonDto::fromDomainModel(
				$person,
				$person->imageId !== null ? $images?->findById($person->imageId) : null,
			);
		}

        return PersonDtoCollection::from(...$items);
    }
}
