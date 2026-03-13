<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Application\Service;

use Contexis\Events\Booking\Domain\ValueObjects\TicketBookingsMap;
use Contexis\Events\Event\Application\DTOs\TicketResponse;
use Contexis\Events\Event\Application\DTOs\TicketResponseCollection;
use Contexis\Events\Event\Domain\Ticket;
use Contexis\Events\Event\Domain\TicketCollection;

final class PrepareBookingTicketLimits
{
    public function map(TicketCollection $tickets, TicketBookingsMap $ticketBookingsMap, ?int $overallCapacity): TicketResponseCollection
    {
        $remainingOverallCapacity = $this->resolveRemainingOverallCapacity($overallCapacity, $ticketBookingsMap);
        $ticketResponses = [];

        foreach ($tickets as $ticket) {
            $ticketResponses[] = $this->mapSingleTicket($ticket, $ticketBookingsMap, $remainingOverallCapacity);
        }

        return TicketResponseCollection::from(...$ticketResponses);
    }

    private function mapSingleTicket(
        Ticket $ticket,
        TicketBookingsMap $ticketBookingsMap,
        ?int $remainingOverallCapacity
    ): TicketResponse {
        $bookedCount = $ticketBookingsMap->getStatsFor($ticket->id)->getBookedCount();
        $remainingTickets = $ticket->capacity === null ? null : max(0, $ticket->capacity - $bookedCount);
        $ticketLimitPerBooking = $ticket->max > 0 ? $ticket->max : null;

        return TicketResponse::fromDomainModel(
            ticket: $ticket,
            remainingTickets: $remainingTickets,
            ticketLimitPerBooking: $ticketLimitPerBooking,
            remainingOverallCapacity: $remainingOverallCapacity,
            bookingLimit: $this->resolveBookingLimit($ticketLimitPerBooking, $remainingTickets, $remainingOverallCapacity),
        );
    }

    private function resolveBookingLimit(?int $ticketLimitPerBooking, ?int $remainingTickets, ?int $remainingOverallCapacity): ?int
    {
        $limits = array_filter(
            [$ticketLimitPerBooking, $remainingTickets, $remainingOverallCapacity],
            static fn(?int $value): bool => $value !== null
        );

        if ($limits === []) {
            return null;
        }

        return min($limits);
    }

    private function resolveRemainingOverallCapacity(?int $overallCapacity, TicketBookingsMap $ticketBookingsMap): ?int
    {
        if ($overallCapacity === null) {
            return null;
        }

        return max(0, $overallCapacity - $ticketBookingsMap->getTotalBookedCount());
    }
}
