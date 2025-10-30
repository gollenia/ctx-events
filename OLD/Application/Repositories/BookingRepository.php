<?php

namespace Contexis\Events\Application\Repositories;

interface BookingRepository {
	public function find_by_id(int $id);
	public function save($booking): void;
	public function delete($booking): void;
}