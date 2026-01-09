<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application;

use Contexis\Events\Event\Application\EventDto;
use Contexis\Events\Event\Domain\ValueObjects\EventSpaces;
use Contexis\Events\Form\Application\FormDto;

final class PrepareBookingDto
{
	public function __construct(
		public EventDto $eventDto,
		public EventSpaces $spaces,
		public TicketDtoCollection $ticketsDto,
		public FormDto $bookingForm,
		public FormDto $attendeeForm,
	)
	{
	}
}