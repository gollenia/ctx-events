<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Event\Application\DTOs\EventCalendarCriteria;
use Contexis\Events\Event\Application\DTOs\EventCalendarEntry;

interface EventCalendarRepository
{
	/**
	 * @return array<int, EventCalendarEntry>
	 */
	public function search(EventCalendarCriteria $criteria): array;
}
