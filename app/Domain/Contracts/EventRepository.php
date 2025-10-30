<?php

namespace Contexis\Events\Domain\Contracts;

use Contexis\Events\Domain\Collections\EventCollection;
use Contexis\Events\Domain\Models\Event;

interface EventRepository
{
	public function find_by_id(int $id): ?Event;
	public function find_by_criteria(EventCriteria $criteria): EventCollection;
	public function count_by_criteria(EventCriteria $criteria): int;
	public function get_capacity(int $event_id): int;
}