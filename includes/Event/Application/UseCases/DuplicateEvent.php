<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application\UseCases;

use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Shared\Application\Contracts\UseCase;

final class DuplicateEvent implements UseCase
{
	public function __construct(
		private readonly EventRepository $eventRepository,
	) {
	}

	/**
	 * @return array<EventId>
	 */
	public function execute(EventId $eventId, \DateTimeImmutable ...$startDates): array
	{
		$duplicatedEventIds = [];
		foreach ($startDates as $startDate) {
			$duplicatedEventId = $this->eventRepository->duplicateAt($eventId, $startDate);
			if ($duplicatedEventId !== null) {
				$duplicatedEventIds[] = $duplicatedEventId;
			}
		}

		return $duplicatedEventIds;
	}

}
