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

    public static function system(string $name, int $id = 0): self
    {
        return new self($id, $name);
    }

    public static function gateway(string $gateway): self
    {
        $normalizedGateway = trim($gateway);

        if ($normalizedGateway === '') {
            return self::system('Gateway webhook');
        }

        return self::system(sprintf('%s webhook', ucfirst($normalizedGateway)));
    }

	/**
	 * @param array<string, mixed> $data
	 */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            name: (string) ($data['name'] ?? ''),
        );
    }

	/**
	 * @return array{id: int, name: string}
	 */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
