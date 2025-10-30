<?php

namespace Contexis\Events\Domain\ValueObjects;

use DateTimeImmutable;
use DateTimeZone;
use DateInterval;
use DomainException;


final class EventSchedule {
	public function __construct(
		public readonly DateTimeImmutable $start,
		public readonly DateTimeImmutable $end,
		public readonly bool $all_day,
		public readonly DateTimeZone $timezone
	) {
		if ($end <= $start) {
            throw new DomainException('End must be after start.');
        }

		if ($all_day) {
			$this->start = self::at_start_of_day($start, $timezone);
			$this->end = self::at_end_of_day($end, $timezone);
        }
	}

	private static function at_start_of_day(DateTimeImmutable $d, DateTimeZone $tz): DateTimeImmutable {
        return new DateTimeImmutable($d->format('Y-m-d').' 00:00:00', $tz);
    }

	private static function at_end_of_day(DateTimeImmutable $d, DateTimeZone $tz): DateTimeImmutable {
		return new DateTimeImmutable($d->format('Y-m-d').' 23:59:59', $tz);
	}

	public function duration(): DateInterval {
        return $this->start->diff($this->end);
    }

	 public function has_started(): bool {
        return $this->start <= new DateTimeImmutable('now', $this->timezone);
    }

    public function has_ended(): bool {
        return $this->end <= new DateTimeImmutable('now', $this->timezone);
    }

	public function is_ongoing(): bool {
		$now = new DateTimeImmutable('now', $this->timezone);
		return $this->start <= $now && $now <= $this->end;
    }
}