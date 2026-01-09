<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Application\Service;

use Contexis\Events\Event\Application\EventCriteria;
use Contexis\Events\Event\Application\TicketDto;
use Contexis\Events\Event\Application\TicketDtoCollection;
use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\Ticket;
use Contexis\Events\Event\Domain\Enums\TicketScope;

final class EventTickets
{
    public function __construct(
        private readonly TicketScope $scope
    ) {
    }

    public static function onlyBookable(): self
    {
        return new self(TicketScope::BOOKABLE_ONLY);
    }

    public static function all(): self
    {
        return new self(TicketScope::ALL);
    }

    public function getAllowedTickets(Event $event): ?TicketDtoCollection
    {
        $tickets = $event->tickets;
        if ($tickets === null) {
            return null;
        }

        $now = new \DateTimeImmutable();

        $items = [];

        foreach ($tickets as $ticket) {
            if ($this->scope === TicketScope::BOOKABLE_ONLY && !$ticket->isBookable($now)) {
                continue;
            }

            $items[] = TicketDto::fromDomainModel($ticket);
        }

        return TicketDtoCollection::fromArray($items);
    }
}
