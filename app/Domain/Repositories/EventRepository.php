<?php

namespace Contexis\Events\Domain\Repositories;

interface EventRepository {
	public function by_id(int $id) : ?\Contexis\Events\Domain\Models\Event;
	public function query(array $args): ?\Contexis\Events\Domain\Collections\EventCollection;
}