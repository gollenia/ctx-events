<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Domain\ValueObjects;

final readonly class BookingNote
{
    public function __construct(
        public string $text,
        public string $date,
        public string $author = '',
    ) {
    }

    public static function create(string $text, string $author = ''): self
    {
        return new self(
            text: $text,
            date: (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            author: $author,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            text: (string) ($data['text'] ?? ''),
            date: (string) ($data['date'] ?? ''),
            author: (string) ($data['author'] ?? ''),
        );
    }
}
