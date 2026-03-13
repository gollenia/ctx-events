<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Domain\ValueObjects;

final readonly class Actor
{
    public function __construct(
        public int $id,
        public string $name,
    ) {
    }

    public static function anonymous(): self
    {
        return new self(0, '');
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            name: (string) ($data['name'] ?? ''),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
