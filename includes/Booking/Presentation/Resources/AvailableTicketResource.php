<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Presentation\Resources;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final readonly class AvailableTicketResource implements \JsonSerializable
{
    public function __construct(
        public string $id,
        public string $name,
        public int $price,
        public ?int $bookingLimit = null,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'bookingLimit' => $this->bookingLimit,
        ];
    }
}
