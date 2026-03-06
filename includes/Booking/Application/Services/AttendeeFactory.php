<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Application\Services;

use Contexis\Events\Booking\Domain\Attendee;
use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Event\Domain\TicketCollection;
use Contexis\Events\Event\Domain\ValueObjects\TicketId;

final class AttendeeFactory
{
    /**
     * @param array<int, array{ticket_id: string, first_name?: string, last_name?: string, metadata?: array}> $payload
     */
    public function fromPayload(array $payload, TicketCollection $tickets): AttendeeCollection
    {
        if ($payload === []) {
            throw new \DomainException('At least one attendee is required.');
        }

        $ticketPriceIndex = $this->buildTicketPriceIndex($tickets);
        $attendees = [];

        foreach ($payload as $item) {
            $ticketId = (string) ($item['ticket_id'] ?? '');
            if ($ticketId === '') {
                throw new \DomainException('Attendee is missing ticket_id.');
            }

            $ticketPrice = $ticketPriceIndex[$ticketId] ?? null;
            if ($ticketPrice === null) {
                throw new \DomainException("Ticket not found: {$ticketId}");
            }

            $firstName = trim((string) ($item['first_name'] ?? '')) ?: null;
            $lastName = trim((string) ($item['last_name'] ?? '')) ?: null;

            $metadata = is_array($item['metadata'] ?? null) ? $item['metadata'] : [];
            $birthDate = isset($metadata['birth_date']) && is_string($metadata['birth_date'])
                ? \DateTimeImmutable::createFromFormat('Y-m-d', $metadata['birth_date']) ?: null
                : null;

            if ($birthDate !== null) {
                unset($metadata['birth_date']);
            }

            $resolvedTicketId = TicketId::from($ticketId)
                ?? throw new \DomainException("Invalid ticket_id: {$ticketId}");

            $attendees[] = new Attendee(
                ticketId: $resolvedTicketId,
                ticketPrice: $ticketPrice,
                firstName: $firstName,
                lastName: $lastName,
                birthDate: $birthDate,
                metadata: $metadata,
            );
        }

        return new AttendeeCollection(...$attendees);
    }

    /** @return array<string, \Contexis\Events\Shared\Domain\ValueObjects\Price> */
    private function buildTicketPriceIndex(TicketCollection $tickets): array
    {
        $index = [];
        foreach ($tickets as $ticket) {
            $index[$ticket->id->toString()] = $ticket->price;
        }
        return $index;
    }
}
