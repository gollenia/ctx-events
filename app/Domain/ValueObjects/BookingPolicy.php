<?php

namespace Contexis\Events\Domain\ValueObjects;

use Contexis\Events\Models\Booking;
use DateTimeImmutable;

final class BookingPolicy {
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

	public static function create_disabled(): self {
		$now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        return new self(false, null, null, $now, $now);
	}

	public static function create_from_values(
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

	public function start(): DateTimeImmutable {
		return $this->start ?? $this->event_created_at;
	}

	public function end(): DateTimeImmutable {
		return $this->end ?? $this->event_start;
	}

	public function enabled(): bool {
		return $this->enabled;
	}

	public function can_book_at(DateTimeImmutable $now): BookingDecision {
		if(!$this->enabled) return BookingDecision::deny(BookingDenyReason::DISABLED);
		if($now < $this->start()) return BookingDecision::deny(BookingDenyReason::NOT_STARTED);
		if($now > $this->end()) return BookingDecision::deny(BookingDenyReason::ENDED);
		return BookingDecision::allow();
	}

	public function can_book(): BookingDecision {
		return $this->can_book_at(new DateTimeImmutable());
	}
}
