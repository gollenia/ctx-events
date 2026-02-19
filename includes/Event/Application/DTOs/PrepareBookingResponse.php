<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application;

use Contexis\Events\Event\Application\EventResponse;
use Contexis\Events\Event\Domain\ValueObjects\EventSpaces;
use Contexis\Events\Form\Application\FormDto;

final class PrepareBookingResponse
{
	public function __construct(
		public EventResponse $eventResponse,
		public EventSpaces $spaces,
		public TicketResponseCollection $ticketsDto,
		public FormDto $bookingForm,
		public FormDto $attendeeForm,
	)
	{
	}
}