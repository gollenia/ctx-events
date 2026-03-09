<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Domain;

use Contexis\Events\Shared\Domain\Abstract\Collection;

final readonly class AttendeeCollection extends Collection
{
    public function __construct(Attendee ...$attendees)
    {
        $this->items = $attendees;
    }

	public function getTicketIds(): array
    {
        return array_map(fn(Attendee $a) => $a->ticketId->toString(), $this->items);
    }

	public function countTicketsById(): array
	{
		$counts = [];
		foreach ($this->items as $attendee) {
			$ticketId = $attendee->ticketId->value;
			if (!isset($counts[$ticketId])) {
				$counts[$ticketId] = 0;
			}
			$counts[$ticketId]++;
		}
		return $counts;
	}
}
