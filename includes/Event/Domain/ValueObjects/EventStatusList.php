<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Domain\ValueObjects;

use Contexis\Events\Event\Domain\Enums\EventStatus;
use InvalidArgumentException;

final readonly class EventStatusList
{
    /** @param list<EventStatus> $statuses */
    private function __construct(private array $statuses)
    {
        $values = array_map(static fn (EventStatus $status): string => $status->value, $this->statuses);
        if (count($values) !== count(array_unique($values))) {
            throw new InvalidArgumentException('An event status may only be selected once.');
        }
    }

    public static function of(EventStatus ...$statuses): self
    {
        return new self($statuses);
    }

    public static function public(): self
    {
        return new self([EventStatus::Published]);
    }

    public static function defaultAdmin(): self
    {
        return new self([
            EventStatus::Published,
            EventStatus::Future,
            EventStatus::Draft,
            EventStatus::Pending,
            EventStatus::Private,
            EventStatus::Cancelled,
        ]);
    }

    public function contains(EventStatus $status): bool
    {
        return in_array($status, $this->statuses, true);
    }

    /** @return list<EventStatus> */
    public function all(): array
    {
        return $this->statuses;
    }
}
