<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application\UseCases;

use Contexis\Events\Event\Application\Contracts\EventCalendarRepository;
use Contexis\Events\Event\Application\DTOs\EventCalendarCriteria;

final class GetEventCalendar
{
	public function __construct(
		private EventCalendarRepository $calendarRepository,
	) {
	}

	public function execute(
		\DateTimeImmutable $startDate,
		\DateTimeImmutable $endDate,
		array $categories = [],
		?int $locationId = null,
		?int $personId = null,
	): array {
		$criteria = new EventCalendarCriteria(
			startDate: $startDate,
			endDate: $endDate,
			categories: $categories,
			locationId: $locationId,
			personId: $personId,
		);

		return $this->calendarRepository->search($criteria);
	}
}
