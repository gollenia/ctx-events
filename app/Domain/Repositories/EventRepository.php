<?php

namespace Contexis\Events\Domain\Repositories;

interface EventRepository {
	public function find(int $id) : ?\Contexis\Events\Domain\Models\Event;
	public function where(array $args): self;
	public function first(): ?\Contexis\Events\Domain\Models\Event;
	public function get(): \Contexis\Events\Domain\Collections\EventCollection;
	public function order(string $orderby, string $order = 'ASC'): self;
	public function limit(int $limit): self;
	public function page(int $page): self;
	public function offset(int $offset): self;
	public function count(): int;
}