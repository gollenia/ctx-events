<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application\DTOs;

final readonly class EventProgramData
{
	/**
	 * @param array<int, EventCalendarEntry> $events
	 */
	public function __construct(
		public string $mode,
		public \DateTimeImmutable $startDate,
		public \DateTimeImmutable $endDate,
		public array $events,
	) {
	}
}
