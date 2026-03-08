<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Presentation\Resources;

use Contexis\Events\Booking\Application\DTOs\BookingListItem;
use Contexis\Events\Shared\Presentation\Contracts\Resource;

final readonly class BookingListItemResource implements Resource
{
    public function __construct(
        public int $id,
        public string $reference,
        public string $email,
        public array $name,
        public array $event,
        public int $status,
        public int $price,
        public int $donation,
        public int $spaces,
        public ?array $gateway,
        public array $tickets,
        public string $date,
    ) {
    }

    public static function fromDTO(BookingListItem $item): self
    {
        $gateway = $item->gateway !== null
            ? ['slug' => $item->gateway, 'name' => $item->gatewayName ?? $item->gateway]
            : null;

        $tickets = [];
        foreach ($item->ticketBreakdown as $ticketId => $count) {
            $tickets[] = ['ticketId' => $ticketId, 'count' => $count];
        }

        return new self(
            id: $item->id,
            reference: $item->reference,
            email: $item->email,
            name: ['first' => $item->firstName, 'last' => $item->lastName],
            event: ['id' => $item->eventId, 'title' => $item->eventTitle],
            status: $item->status,
            price: $item->finalPrice,
            donation: $item->donationAmount,
            spaces: $item->spaces,
            gateway: $gateway,
            tickets: $tickets,
            date: $item->bookingTime->format(DATE_ATOM),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id'        => $this->id,
            'reference' => $this->reference,
            'email'     => $this->email,
            'name'      => $this->name,
            'event'     => $this->event,
            'status'    => $this->status,
            'price'     => $this->price,
            'donation'  => $this->donation,
            'spaces'    => $this->spaces,
            'gateway'   => $this->gateway,
            'tickets'   => $this->tickets,
            'date'      => $this->date,
        ];
    }
}
