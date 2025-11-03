<?php

namespace Contexis\Events\Domain\Repositories;

interface AttendeeRepository {
	public function find(string $id);
}