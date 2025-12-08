<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Application;

use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\EventCollection;
use Contexis\Events\Event\Domain\TicketCollection;
use Contexis\Events\Location\Application\LocationDtoCollection;
use Contexis\Events\Person\Application\PersonDtoCollection;
use Contexis\Events\Shared\Application\Contracts\DTO;
use Contexis\Events\Shared\Domain\Abstract\DtoCollection;

final class TicketDtoCollection extends DtoCollection
{
    public function __construct(
        TicketDto ...$tickets
    ) {
        $this->items = $tickets;
    }

    public static function fromDomainCollection(
        TicketCollection $collection
    ): TicketDtoCollection {
        return new TicketDtoCollection(...$collection->toArray());
    }
}
