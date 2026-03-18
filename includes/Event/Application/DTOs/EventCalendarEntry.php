<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application\DTOs;

final readonly class EventCalendarEntry
{
	/**
	 * @param array<int> $categoryIds
	 */
	public function __construct(
		public int $id,
		public string $title,
		public string $description,
		public \DateTimeImmutable $startDate,
		public \DateTimeImmutable $endDate,
		public array $categoryIds,
		public ?string $locationName,
		public ?string $personName,
	) {
	}
}
