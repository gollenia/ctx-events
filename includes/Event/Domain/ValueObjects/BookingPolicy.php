<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Domain\ValueObjects;

use Contexis\Events\Event\Domain\Enums\BookingDenyReason;
use DateTimeImmutable;

final class BookingPolicy
{
    private function __construct(
        private readonly bool $enabled,
        private readonly ?DateTimeImmutable $start,
        private readonly ?DateTimeImmutable $end,
        private readonly ?DateTimeImmutable $event_created_at,
        private readonly ?DateTimeImmutable $event_start,
    ) {
        $s = $this->start() ;
        $e = $this->end()   ;
        if ($e < $s) {
            throw new \DomainException('Booking window invalid: end before start.');
        }
    }

    public static function createWithDisabledBookings(): self
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        return new self(false, null, null, $now, $now);
    }

    public static function create(
        bool $enabled,
        ?DateTimeImmutable $start,
        ?DateTimeImmutable $end,
        DateTimeImmutable $event_created_at,
        DateTimeImmutable $event_start
    ): self {
        return new self(
            enabled: $enabled,
            start: $start,
            end: $end,
            event_created_at: $event_created_at,
            event_start: $event_start
        );
    }

    public function start(): DateTimeImmutable
    {
        return $this->start ?? $this->event_created_at;
    }

    public function end(): DateTimeImmutable
    {
        return $this->end ?? $this->event_start;
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function canBookAt(DateTimeImmutable $now): BookingDecision
    {
        if (!$this->enabled) {
            return BookingDecision::deny(BookingDenyReason::DISABLED);
        }
        if ($now < $this->start()) {
            return BookingDecision::deny(BookingDenyReason::NOT_STARTED);
        }
        if ($now > $this->end()) {
            return BookingDecision::deny(BookingDenyReason::ENDED);
        }
        return BookingDecision::allow();
    }	

    public function canBook(): BookingDecision
    {
        return $this->canBookAt(new DateTimeImmutable());
    }

	public function toArray(DateTimeImmutable $now): array
	{
		return [
			'enabled' => $this->enabled,
			'canBook' => $this->canBookAt($now),
			'start'   => $this->start()->format(DATE_ATOM),
			'end'     => $this->end()->format(DATE_ATOM),
		];
	}
}
