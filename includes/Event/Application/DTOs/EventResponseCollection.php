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

final readonly class EventResponseCollection extends DtoCollection
{
    public static function from(EventResponse ...$items): self
	{
		return new self($items);
	}
}
