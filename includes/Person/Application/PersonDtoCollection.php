<?php
declare(strict_types=1);

namespace Contexis\Events\Person\Application;

use Contexis\Events\Person\Domain\Person;
use Contexis\Events\Person\Domain\PersonCollection;
use Contexis\Events\Shared\Domain\Abstract\DtoCollection;

final readonly class PersonDtoCollection extends DtoCollection
{
    public static function from(
        PersonDto ...$persons
    ): self {
        return new self($persons);
    }

    public static function fromDomainCollection(PersonCollection $collection): PersonDtoCollection
    {
        $items = [];
        foreach ($collection as $item) {
            $items[] = PersonDto::fromDomainModel($item);
        }
        return PersonDtoCollection::from(...$items);
    }

    public function findById(int $id): ?PersonDto
    {
        foreach ($this->items as $personDto) {
            if ($personDto->id === $id) {
                return $personDto;
            }
        }
        return null;
    }
}
