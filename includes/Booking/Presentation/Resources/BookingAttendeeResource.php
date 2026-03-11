<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Presentation\Resources;

use Contexis\Events\Booking\Domain\Attendee;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final readonly class BookingAttendeeResource implements \JsonSerializable
{
    public function __construct(
        public string $ticketId,
        public ?PersonName $name,
        public array $metadata,
    ) {
    }

	public static function fromAttendee(Attendee $attendee): self
	{
		return new self(
			ticketId: $attendee->ticketId->toString(),
			name: $attendee->name,
			metadata: $attendee->metadata,
		);
	}

    public function jsonSerialize(): array
    {
        return [
            'ticketId' => $this->ticketId,
            'name'     => $this->name?->toArray(),
            'metadata' => $this->metadata,
        ];
    }
}
