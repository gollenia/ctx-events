<?php

namespace Contexis\Events\Infrastructure\Persistence;

use Contexis\Events\Domain\Contracts\AttendeeRepository;

class DbAttendeeRepository implements AttendeeRepository {
	const TABLE_NAME = 'event_attendees';

	// Implementation of AttendeeRepository methods would go here
}