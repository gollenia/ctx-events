<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Presentation\Resources;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final readonly class BookingEventResource implements \JsonSerializable
{
    public function __construct(
        public int $id,
        public string $title,
    ) {
    }

    public function jsonSerialize(): array
    {
        return ['id' => $this->id, 'title' => $this->title];
    }
}
