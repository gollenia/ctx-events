<?php

namespace Contexis\Events\Domain\Contracts;

use Contexis\Events\Domain\Collections\EventCollection;
use Contexis\Events\Domain\Models\Event;

interface EventRepository
{
	public function find(int $id): void;
	public function where(EventCriteria $criteria): void;
	public function first(): ?Event;
	public function get(): EventCollection;
	public function count_by_criteria(EventCriteria $criteria): int;
}