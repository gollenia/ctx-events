<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Infrastructure;

use Contexis\Events\Booking\Domain\AttendeeRepository;

class DbAttendeeRepository implements AttendeeRepository
{
    const TABLE_NAME = 'event_attendees';

    // Implementation of AttendeeRepository methods would go here
}
