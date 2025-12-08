<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Shared\Domain\Abstract\Collection;

final class EventCollection extends Collection
{
    public function __construct(
        Event ...$events
    ) {
        $this->items = $events;
    }
}
