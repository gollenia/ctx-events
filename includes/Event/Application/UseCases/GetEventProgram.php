<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application\UseCases;

use Contexis\Events\Event\Application\Contracts\EventCalendarRepository;
use Contexis\Events\Event\Application\DTOs\EventCalendarCriteria;
use Contexis\Events\Event\Application\DTOs\EventProgramData;

final class GetEventProgram
{
	public function __construct(
		private EventCalendarRepository $calendarRepository,
	) {
	}

	public function execute(string $mode, int $offset, ?int $category): EventProgramData
	{
		$normalizedMode = in_array($mode, ['week', 'month', 'year'], true) ? $mode : 'month';
		$normalizedOffset = max(0, min(24, $offset));
		[$startDate, $endDate] = $this->getDateRange($normalizedMode, $normalizedOffset);

		$criteria = new EventCalendarCriteria(
			startDate: $startDate,
			endDate: $endDate,
			categories: $category && $category > 0 ? [$category] : [],
		);

		return new EventProgramData(
			mode: $normalizedMode,
			startDate: $startDate,
			endDate: $endDate,
			events: $this->calendarRepository->search($criteria),
		);
	}

	/**
	 * @return array{0: \DateTimeImmutable, 1: \DateTimeImmutable}
	 */
	private function getDateRange(string $mode, int $offset): array
	{
		$start = new \DateTimeImmutable('now', wp_timezone());

		if ($mode === 'week') {
			$start = $start->modify('monday this week')->setTime(0, 0, 0);
			if ($offset > 0) {
				$start = $start->modify(sprintf('+%d weeks', $offset));
			}
			return [$start, $start->modify('sunday this week')->setTime(23, 59, 59)];
		}

		if ($mode === 'year') {
			$start = $start->modify('first day of January')->setTime(0, 0, 0);
			if ($offset > 0) {
				$start = $start->modify(sprintf('+%d years', $offset));
			}
			return [$start, $start->modify('last day of December')->setTime(23, 59, 59)];
		}

		$start = $start->modify('first day of this month')->setTime(0, 0, 0);
		if ($offset > 0) {
			$start = $start->modify(sprintf('+%d months', $offset));
		}

		return [$start, $start->modify('last day of this month')->setTime(23, 59, 59)];
	}
}
