<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Presentation\Resources;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final readonly class BookingNoteResource implements \JsonSerializable
{
    public function __construct(
        public string $text,
        public string $date,
        public string $author,
    ) {
    }

    public function jsonSerialize(): array
    {
        return ['text' => $this->text, 'date' => $this->date, 'author' => $this->author];
    }
}
