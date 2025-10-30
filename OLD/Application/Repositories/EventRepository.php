<?php

namespace Contexis\Events\Application\Repositories;

interface EventRepository {
	public function find_by_id(int $id);
	public function save($event): void;
	public function delete($event): void;
}

