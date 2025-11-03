<?php

namespace Contexis\Events\Domain\Repositories;

interface BookingRepository {
	public function find(string $id);
}
