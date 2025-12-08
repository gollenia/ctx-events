<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Application\Service;

use Contexis\Events\Event\Application\EventCriteria;
use Contexis\Events\Event\Application\TicketDto;
use Contexis\Events\Event\Application\TicketDtoCollection;
use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\Ticket;

final class EventTickets
{
    public function __construct(
        private readonly bool $allTickets
    ) {
    }

    public static function create(bool $allTickets): self
    {
        return new self($allTickets);
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
            if (!$this->allTickets && !$ticket->isBookable($now)) {
                continue;
            }

            $items[] = TicketDto::fromDomain($ticket);
        }

        return TicketDtoCollection::fromArray($items);
    }
}
