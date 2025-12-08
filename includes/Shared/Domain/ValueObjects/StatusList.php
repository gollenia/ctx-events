<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Domain\ValueObjects;

final class StatusList
{
    private array $statuses;

    private function __construct(Status ...$statuses)
    {
        $this->statuses = $statuses;
    }

    public static function of(Status ...$statuses): self
    {
        return new self(...$statuses);
    }

    public static function all(): self
    {
        return new self(...Status::cases());
    }

    public static function public(): self
    {
        return new self(Status::Published);
    }

    public static function nonDeleted(): self
    {
        return new self(Status::Published, Status::Future, Status::Draft, Status::Private);
    }

    public static function fromStrings(array $statuses): self
    {
        $statusEnums = array_map(fn(string $s) => Status::from($s), $statuses);
        return new self(...$statusEnums);
    }

    public static function defaultAdmin(): self
    {
        return new self(Status::Published, Status::Future, Status::Draft, Status::Private);
    }

    public function allStatuses(): array
    {
        return $this->statuses;
    }

    public function toArray(): array
    {
        return array_map(fn(Status $s) => $s->value, $this->statuses);
    }

    public function isEmpty(): bool
    {
        return $this->statuses === [];
    }
}
