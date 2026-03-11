<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Presentation\Resources;

use Contexis\Events\Booking\Application\DTOs\EditBookingResponse;
use Contexis\Events\Booking\Domain\ValueObjects\BookingNote;
use Contexis\Events\Event\Application\DTOs\TicketResponse;
use Contexis\Events\Shared\Presentation\Contracts\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'BookingDetail')]
final readonly class EditBookingResource implements Resource
{
    public function __construct(
        public string $reference,
        public string $date,
        public int $status,
        public \Contexis\Events\Shared\Domain\ValueObjects\PersonName $name,
        public string $email,
        public ?string $gateway,
        public BookingEventResource $event,
        public array $registration,
        /** @var BookingAttendeeResource[] */
        public array $attendees,
        public PriceSummaryResource $price,
        /** @var BookingNoteResource[] */
        public array $notes,
        /** @var AvailableTicketResource[] */
        public array $availableTickets,
    ) {
    }

    public static function fromDTO(EditBookingResponse $response): self
    {
        return new self(
            reference: $response->booking->reference->toString(),
            date: $response->booking->bookingTime->format(DATE_ATOM),
            status: $response->booking->status->value,
            name: $response->booking->name,
            email: $response->booking->email->toString(),
            gateway: $response->booking->gateway,
            event: new BookingEventResource($response->eventId->toInt(), $response->eventTitle),
            registration: $response->registrationForm->all(),
            attendees: array_map(
                static fn(\Contexis\Events\Booking\Domain\Attendee $a) => BookingAttendeeResource::fromAttendee($a),
                iterator_to_array($response->booking->attendees),
            ),
            price: PriceSummaryResource::fromPriceSummary($response->booking->priceSummary),
            notes: array_map(
                static fn(BookingNote $n) => new BookingNoteResource($n->text, $n->date, $n->author),
                iterator_to_array($response->notes),
            ),
            availableTickets: array_map(
                static fn(TicketResponse $t) => new AvailableTicketResource($t->id, $t->name, $t->price->amountCents),
                $response->availableTickets->toArray(),
            ),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'reference'        => $this->reference,
            'date'             => $this->date,
            'status'           => $this->status,
            'name'             => $this->name->toArray(),
            'email'            => $this->email,
            'gateway'          => $this->gateway,
            'event'            => $this->event,
            'registration'     => $this->registration,
            'attendees'        => $this->attendees,
            'price'            => $this->price,
            'notes'            => $this->notes,
            'availableTickets' => $this->availableTickets,
        ];
    }
}
