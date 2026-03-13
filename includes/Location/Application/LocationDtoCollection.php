<?php
declare(strict_types=1);

namespace Contexis\Events\Location\Application;

use IteratorAggregate;
use ArrayIterator;
use Contexis\Events\Location\Domain\LocationCollection;
use Contexis\Events\Location\Domain\LocationId;
use Contexis\Events\Shared\Domain\Abstract\DtoCollection;

final readonly class LocationDtoCollection extends DtoCollection
{
    public static function from(LocationDto ...$locations): self
    {
        return new self($locations);
    }

    public static function fromDomainCollection(LocationCollection $collection): LocationDtoCollection
    {
        $items = [];
        foreach ($collection as $item) {
            $items[] = LocationDto::fromDomainModel($item);
        }
        return LocationDtoCollection::from(...$items);
    }

    public function findById(LocationId $id): ?LocationDto
    {
        foreach ($this->items as $locationDto) {
            if ($locationDto->id === $id->toInt()) {
                return $locationDto;
            }
        }
        return null;
    }

    public function toArray(): array
    {
        return array_map(fn(LocationDto $dto) => $dto->toArray(), $this->items);
    }
}
