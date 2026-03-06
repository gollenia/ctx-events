<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Shared\Domain\Abstract\Collection;

final readonly class EventCollection extends Collection
{
    public function __construct(
        Event ...$events
    ) {
        $this->items = $events;
    }

	public function getIds(): array
	{
		return array_map(fn(Event $event) => $event->id, $this->items);
	}
}
