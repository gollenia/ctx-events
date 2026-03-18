<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Shared\Domain\Abstract\Collection;

final readonly class EventCollection extends Collection
{
    public static function from(Event ...$events): self
    {
        return new self($events);
    }

	/**
	 * @return array<int>
	 */
	public function getIds(): array
	{
		return array_map(fn(Event $event) => $event->id, $this->items);
	}
}
