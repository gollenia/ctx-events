<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Application\DTOs;

use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\EventCollection;
use Contexis\Events\Event\Domain\TicketCollection;
use Contexis\Events\Location\Application\LocationDtoCollection;
use Contexis\Events\Person\Application\PersonDtoCollection;
use Contexis\Events\Shared\Application\Contracts\DTO;
use Contexis\Events\Shared\Domain\Abstract\DtoCollection;
use Contexis\Events\Shared\Domain\ValueObjects\Price;

final class TicketResponseCollection extends DtoCollection
{
	public ?Price $lowestAvailablePrice = null;
    public function __construct(
        TicketResponse ...$tickets
    ) {
        $this->items = $tickets;
    }

    public static function fromDomainCollection(
        TicketCollection $collection,
		\DateTimeImmutable $now
    ): TicketResponseCollection {
        $result = new TicketResponseCollection(
			...$collection->toArray()
        );
		$result->lowestAvailablePrice = $collection->getLowestAvailablePrice($now);
		return $result;
    }
}
	