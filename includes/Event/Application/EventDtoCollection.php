<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Application;

use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\EventCollection;
use Contexis\Events\Location\Application\LocationDtoCollection;
use Contexis\Events\Person\Application\PersonDtoCollection;
use Contexis\Events\Shared\Application\Contracts\DTO;
use Contexis\Events\Shared\Domain\Abstract\DtoCollection;
use Contexis\Events\Shared\Application\ValueObjects\Pagination;

final class EventDtoCollection extends DtoCollection
{
    public ?Pagination $pagination = null;

    public function __construct(
        EventDto ...$events
    ) {
        $this->items = $events;
    }

    public static function fromDomainCollection(
        EventCollection $collection
    ): EventDtoCollection {
        return new EventDtoCollection(...$collection->toArray());
    }

    public function withPagination(Pagination $pagination): self
    {
        return clone($this, ['pagination' => $pagination]);
    }
}
