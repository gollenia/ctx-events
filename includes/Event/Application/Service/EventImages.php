<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Application\Service;

use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\EventCollection;
use Contexis\Events\Media\Application\ImageDtoCollection;
use Contexis\Events\Media\Domain\ImageRepository;

final class EventImages
{
    public function __construct(
        private readonly ImageRepository $images
    ) {
    }

    public static function create(ImageRepository $images): self
    {
        return new self($images);
    }

    public function preloadDtos(EventCollection $events): ?ImageDtoCollection
    {
        $ids = array_map(function (Event $event) {
            return $event->imageId;
        }, $events->toArray())
          |> array_filter(...)
          |> array_unique(...);

        if ($ids === []) return null;

        $collection = $this->images->findByIds($ids);

        return ImageDtoCollection::fromDomainCollection($collection);
    }
}
