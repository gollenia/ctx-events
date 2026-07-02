<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Application\Services;

use Contexis\Events\Booking\Domain\Attendee;
use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\Enums\AttendeeStatus;
use Contexis\Events\Booking\Domain\ValueObjects\AttendeeId;
use Contexis\Events\Event\Domain\TicketCollection;
use Contexis\Events\Event\Domain\ValueObjects\TicketId;
use Contexis\Events\Person\Domain\Person;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;

final class AttendeeFactory
{
	/** @param array<string, mixed> $payload */
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
            $status = AttendeeStatus::tryFrom((string) ($item['status'] ?? AttendeeStatus::ACTIVE->value))
                ?? AttendeeStatus::ACTIVE;

            $attendees[] = new Attendee(
                ticketId: $ticketId,
                ticketPrice: $ticket->price,
                name: $personName,
				birthDate: $birthDate,
                metadata: $metadata,
                status: $status,
                id: isset($item['id']) ? AttendeeId::from((int) $item['id']) : null,
            );
        }

        return AttendeeCollection::from(...$attendees);
    }

    /**
     * @param array<int, array<string, mixed>> $payload
     */
    public function fromAdminPayload(array $payload, TicketCollection $tickets): AttendeeCollection
    {
        if ($payload === []) {
            throw new \DomainException('At least one attendee is required.');
        }

        return AttendeeCollection::from(...array_map(
            fn (array $item): Attendee => $this->oneFromAdminPayload($item, $tickets),
            $payload,
        ));
    }

	/** @param array<string, mixed> $payload */
    public function oneFromAdminPayload(array $payload, TicketCollection $tickets): Attendee
    {
        $metadata = is_array($payload['metadata'] ?? null) ? $payload['metadata'] : [];
        $nameData = $payload['name'] ?? null;
        $name = is_array($nameData) && (($nameData['firstName'] ?? '') !== '' || ($nameData['lastName'] ?? '') !== '')
            ? new PersonName((string) ($nameData['firstName'] ?? ''), (string) ($nameData['lastName'] ?? ''))
            : $this->getPersonName($metadata);
        $ticketId = TicketId::from((string) ($payload['ticketId'] ?? $payload['ticket_id'] ?? ''));
        $ticket = $tickets->getTicketById($ticketId);
        $status = AttendeeStatus::tryFrom((string) ($payload['status'] ?? AttendeeStatus::ACTIVE->value))
            ?? AttendeeStatus::ACTIVE;
        $ticketPriceData = $payload['ticketPrice'] ?? null;
        $ticketPriceCents = is_array($ticketPriceData)
            ? (int) ($ticketPriceData['amountCents'] ?? $ticket->price->amountCents)
            : (int) ($payload['ticket_price'] ?? $ticket->price->amountCents);

        return new Attendee(
            ticketId: $ticketId,
            ticketPrice: $ticket->price->withAmount($ticketPriceCents),
            name: $name,
			birthDate: $this->getBithdate($metadata),
            metadata: $metadata,
            status: $status,
            id: isset($payload['id']) ? AttendeeId::from((int) $payload['id']) : null,
        );
    }

	/** @param array<string, mixed> $metadata */
	private function getPersonName(array $metadata): ?PersonName
	{
		$firstName = $metadata['first_name'] ?? null;
		$lastName = $metadata['last_name'] ?? null;

		if ($firstName === null || $lastName === null) {
			return null;
		}

		return new PersonName($firstName, $lastName);
	}

	/** @param array<string, mixed> $metadata */
	private function getBithdate(array $metadata): ?\DateTimeImmutable
	{
		if (!isset($metadata['birth_date']) || !is_string($metadata['birth_date'])) {
			return null;
		}

		return \DateTimeImmutable::createFromFormat('Y-m-d', $metadata['birth_date']) ?: null;
	}

   
}
