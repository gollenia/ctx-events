<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application\DTOs;

final readonly class EventCalendarCriteria
{
	/**
	 * @param array<int> $categories
	 */
	public function __construct(
		public \DateTimeImmutable $startDate,
		public \DateTimeImmutable $endDate,
		public array $categories = [],
		public ?int $locationId = null,
		public ?int $personId = null,
	) {
	}
}
