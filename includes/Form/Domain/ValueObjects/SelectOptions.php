<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\ValueObjects;

final class SelectOptions implements \IteratorAggregate, \Countable, \JsonSerializable
{
   	/** @var SelectOption[] */
    private array $options;

    public function __construct(SelectOption ...$options)
    {
        $this->options = $options;
    }

	public static function fromArray(array $options): self
    {
        $options = array_map(fn($option) => SelectOption::fromMixed($option), $options);
        return new self(...$options);
    }

	public function contains(string $value): bool
    {
       foreach ($this->options as $option) {
            if ($option->getEffectiveValue() === $value) {
                return true;
            }
        }
        return false;
    }

	public function toArray(): array
    {
        return array_map(fn($option) => $option->toArray(), $this->options);
    }

	public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->options);
    }

	public function count(): int
    {
        return count($this->options);
    }

	public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}