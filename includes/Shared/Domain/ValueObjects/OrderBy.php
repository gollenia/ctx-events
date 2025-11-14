<?php

namespace Contexis\Events\Shared\Domain\ValueObjects;

final class OrderBy
{
    private function __construct(
        public readonly string $field,
        public readonly ?Order $direction,
        public readonly bool $isMeta = false
    ) {
    }

    public static function fromField(string $field, Order|string $direction = 'ASC'): self
    {
        $dir = $direction instanceof Order ? $direction : Order::from($direction);
        return new self($field, $dir, false);
    }

    public static function fromMeta(string $metaKey, Order|string $direction = 'ASC'): self
    {
        $dir = $direction instanceof Order ? $direction : Order::from($direction);
        return new self($metaKey, $dir, true);
    }

    public function withOrder(Order|string $direction): self
    {
        $dir = $direction instanceof Order ? $direction : Order::from($direction);
        return new self($this->field, $dir, $this->isMeta);
    }

    public function direction(): Order
    {
        return $this->direction ?? Order::DESC;
    }
}
