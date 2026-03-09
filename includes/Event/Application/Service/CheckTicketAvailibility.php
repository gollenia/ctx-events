<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application\Service;

use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\TicketBookingsMap;
use Contexis\Events\Event\Domain\TicketCollection;
use Contexis\Events\Event\Domain\ValueObjects\TicketId;

final class CheckTicketAvailibility
{
    /**
     * @param array<string, int> $requestedCounts ticketId-String → number of requested spots
     * @throws \DomainException
     */
    public function perform(
        AttendeeCollection $attendees,
        TicketBookingsMap $bookingsMap,
		TicketCollection $ticketCollection,
        \DateTimeImmutable $now
    ): void {
		$attendeeTickets = $attendees->countTicketsById();

        foreach ($attendeeTickets as $ticketIdString => $requestedCount) {
            $ticketId = TicketId::from($ticketIdString);
            $ticket = $ticketCollection->getTicketById($ticketId);

            if ($ticket === null) {
                throw new \DomainException("Ticket not found: {$ticketIdString}");
            }

            if (!$ticket->isBookable($now)) {
                throw new \DomainException("Ticket '{$ticket->name}' is currently not available.");
            }
			
            $free = $ticketCollection->getFreeSpacesForTicket($ticketId, $bookingsMap, $now);

			if ($free < $requestedCount) {
				throw new \DomainException(
					"Ticket '{$ticket->name}' has only {$free} free space(s) left, {$requestedCount} requested."
				);
			}
        }
    }
}
