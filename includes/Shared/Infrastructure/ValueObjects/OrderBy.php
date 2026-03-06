<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\ValueObjects;

final class OrderBy
{
    private function __construct(
        public readonly string $field,
        public readonly Order $order = Order::DESC,
        public readonly bool $isMeta = false
    ) {
    }

	public static function default(): self
	{
		return new self('date', Order::DESC, false);
	}

    public static function fromField(string $field, Order $order = Order::DESC): self
    {
        return new self($field, $order, false);
    }

    public static function fromMeta(string $metaKey, Order $order = Order::DESC): self
    {
        return new self($metaKey, $order, true);
    }

    public function withOrder(Order $order): self
    {
        return new self($this->field, $order, $this->isMeta);
    }

    public function order(): Order
    {
        return $this->order;
    }
}
