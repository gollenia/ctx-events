<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Infrastructure\Mapper;

use Contexis\Events\Booking\Domain\Attendee;
use Contexis\Events\Event\Domain\ValueObjects\TicketId;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Contexis\Events\Shared\Domain\ValueObjects\Price;

final class AttendeeMapper
{
    public static function map(array $row): Attendee
    {
        $metadata  = json_decode($row['metadata'] ?? '{}', true) ?? [];
        $birthDate = null;

        if (isset($metadata['birth_date'])) {
            $birthDate = \DateTimeImmutable::createFromFormat('Y-m-d', $metadata['birth_date']) ?: null;
        }

        return new Attendee(
            ticketId: TicketId::from($row['ticket_id']),
            ticketPrice: Price::from(0, Currency::fromCode('EUR')),
            name: isset($row['first_name'], $row['last_name']) ? new PersonName($row['first_name'], $row['last_name']) : null,
            birthDate: $birthDate,
            metadata: $metadata,
        );
    }
}
