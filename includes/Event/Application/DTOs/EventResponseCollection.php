<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Application\DTOs;

use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\EventCollection;
use Contexis\Events\Event\Domain\ValueObjects\EventStatusCounts;
use Contexis\Events\Location\Application\LocationDtoCollection;
use Contexis\Events\Person\Application\PersonDtoCollection;
use Contexis\Events\Shared\Application\Contracts\DTO;
use Contexis\Events\Shared\Domain\Abstract\DtoCollection;
use Contexis\Events\Shared\Application\ValueObjects\Pagination;

final class EventResponseCollection extends DtoCollection
{
    public ?Pagination $pagination = null;
	public ?EventStatusCounts $statusCounts = null;

    public function __construct(
        EventResponse ...$events
    ) {
        $this->items = $events;
    }

    public static function fromDomainCollection(
        EventCollection $collection
    ): EventResponseCollection {
		return new self(...array_map(
			fn(Event $event) => EventResponse::fromDomainModel($event),
			$collection->toArray()
		));
    }

    public function withPagination(Pagination $pagination): self
    {
        return clone($this, ['pagination' => $pagination]);
    }

	public function withStatusCounts(EventStatusCounts $statusCounts): self
	{
		return clone($this, ['statusCounts' => $statusCounts]);
	}

	public function hasStatusCounts(): bool
	{
		return $this->statusCounts !== null;
	}
}
