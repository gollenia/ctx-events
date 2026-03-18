<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Application\Services;

use Contexis\Events\Booking\Domain\Attendee;
use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Event\Domain\TicketCollection;
use Contexis\Events\Event\Domain\ValueObjects\TicketId;
use Contexis\Events\Person\Domain\Person;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;

final class AttendeeFactory
{
    public function fromPayload(array $payload, TicketCollection $tickets): AttendeeCollection
    {
        if ($payload === []) {
            throw new \DomainException('At least one attendee is required.');
        }

        $attendees = [];

        foreach ($payload as $item) {
            $ticketId = TicketId::from($item['ticket_id']);

            $ticket = $tickets->getTicketById($ticketId);

			$metadata = is_array($item['metadata'] ?? null) ? $item['metadata'] : [];

			$personName = $this->getPersonName($metadata);
            $birthDate = $this->getBithdate($metadata);

            $attendees[] = new Attendee(
                ticketId: $ticketId,
                ticketPrice: $ticket->price,
                name: $personName,
				birthDate: $birthDate,
                metadata: $metadata,
            );
        }

        return AttendeeCollection::from(...$attendees);
    }

	private function getPersonName(array $metadata): ?PersonName
	{
		$firstName = $metadata['first_name'] ?? null;
		$lastName = $metadata['last_name'] ?? null;

		if ($firstName === null || $lastName === null) {
			return null;
		}

		return new PersonName($firstName, $lastName);
	}

	private function getBithdate(array $metadata): ?\DateTimeImmutable
	{
		if (!isset($metadata['birth_date']) || !is_string($metadata['birth_date'])) {
			return null;
		}

		return \DateTimeImmutable::createFromFormat('Y-m-d', $metadata['birth_date']) ?: null;
	}

   
}
