<?php

namespace Contexis\Events\Domain\Collections;

use Contexis\Events\Domain\Models\Event;

final class EventCollection extends AbstractTypedCollection
{
	public function __construct(Event ...$events)
	{
		$this->items = $events;
	}
}