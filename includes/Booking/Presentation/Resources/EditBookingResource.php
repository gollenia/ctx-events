<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Presentation\Resources;

use Contexis\Events\Booking\Application\DTOs\EditBookingResponse;
use Contexis\Events\Booking\Domain\ValueObjects\BookingNote;
use Contexis\Events\Booking\Domain\ValueObjects\LogEntry;
use Contexis\Events\Event\Application\DTOs\TicketResponse;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Contexis\Events\Shared\Presentation\Contracts\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'BookingDetail')]
final readonly class EditBookingResource implements Resource
{
    public function __construct(
        public string $reference,
        public string $date,
        public int $status,
        public ?string $gateway,
        public BookingEventResource $event,
        public array $registration,
        /** @var BookingAttendeeResource[] */
        public array $attendees,
        /** @var BookingTransactionResource[] */
        public array $transactions,
        public PriceSummaryResource $price,
		public array $bookingForm,
		public array $attendeeForm,
        /** @var BookingNoteResource[] */
        public array $notes,
        /** @var BookingLogEntryResource[] */
        public array $logEntries,
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
            gateway: $response->booking->gateway,
            event: new BookingEventResource($response->eventId->toInt(), $response->eventTitle),
            registration: $response->booking->registration->all(),
            attendees: array_map(
                static fn(\Contexis\Events\Booking\Domain\Attendee $a) => BookingAttendeeResource::fromAttendee($a),
                iterator_to_array($response->booking->attendees),
            ),
            transactions: array_map(
                static fn(\Contexis\Events\Payment\Domain\Transaction $transaction) => BookingTransactionResource::fromTransaction($transaction),
                iterator_to_array($response->booking->transactions ?? []),
            ),
            price: PriceSummaryResource::from($response->booking->priceSummary),
			bookingForm: $response->registrationForm->toArray(),
			attendeeForm: $response->attendeeForm->toArray(),
            notes: array_map(
                static fn(BookingNote $n) => new BookingNoteResource($n->text, $n->date, $n->author),
                iterator_to_array($response->notes),
            ),
            logEntries: array_map(
                static fn(LogEntry $entry) => BookingLogEntryResource::fromLogEntry($entry),
                iterator_to_array($response->booking->logEntries),
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
            'gateway'          => $this->gateway,
            'event'            => $this->event,
            'registration'     => $this->registration,
            'attendees'        => $this->attendees,
            'transactions'     => $this->transactions,
            'price'            => $this->price,
			'bookingForm'      => $this->bookingForm,
			'attendeeForm'     => $this->attendeeForm,
            'notes'            => $this->notes,
            'logEntries'       => $this->logEntries,
            'availableTickets' => $this->availableTickets,
        ];
    }
}
