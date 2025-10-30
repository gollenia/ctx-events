<?php

namespace Contexis\Events\Domain\Collections;


final class EventCollection
{
	/** @var Event[] */
	public array $events = [];

	public function add(Event $event): void
	{
		$this->events[] = $event;
	}

	/**
	 * @return Event[]
	 */
	public function all(): array
	{
		return $this->events;
	}
}