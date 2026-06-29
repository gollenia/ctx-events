<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Presentation\Resources;

use Contexis\Events\Booking\Domain\Attendee;
use Contexis\Events\Booking\Domain\Enums\AttendeeStatus;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Contexis\Events\Shared\Presentation\Resources\PriceResource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final readonly class BookingAttendeeResource implements \JsonSerializable
{
    public function __construct(
        public ?int $id,
        public string $ticketId,
        public PriceResource $ticketPrice,
        public ?PersonName $name,
        public array $metadata,
        public AttendeeStatus $status,
    ) {
    }

	public static function fromAttendee(Attendee $attendee): self
	{
		return new self(
            id: $attendee->id?->toInt(),
			ticketId: $attendee->ticketId->toString(),
            ticketPrice: PriceResource::from($attendee->ticketPrice),
			name: $attendee->name,
			metadata: $attendee->metadata,
            status: $attendee->status,
		);
	}

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'ticketId' => $this->ticketId,
            'ticketPrice' => $this->ticketPrice,
            'name'     => $this->name?->toArray(),
            'metadata' => $this->metadata,
            'status' => $this->status->value,
        ];
    }
}
