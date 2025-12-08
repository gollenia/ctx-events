<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Domain;

use Contexis\Events\Shared\Domain\Abstract\Collection;

final class AttendeeCollection extends Collection
{
    public function __construct(Attendee ...$attendees)
    {
        $this->items = $attendees;
    }
}
