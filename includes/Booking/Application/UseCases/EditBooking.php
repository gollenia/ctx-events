<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\UseCases;

use Contexis\Events\Booking\Application\DTOs\EditBookingResponse;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Event\Application\DTOs\TicketResponseCollection;
use Contexis\Events\Event\Application\Service\PrepareBookingTicketLimits;
use Contexis\Events\Event\Application\UseCases\PrepareBooking;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\TicketCollection;
use Contexis\Events\Form\Domain\FormRepository;
use Contexis\Events\Payment\Domain\GatewayRepository;
use Contexis\Events\Shared\Application\ValueObjects\UserContext;
use Contexis\Events\Shared\Domain\Contracts\Clock;

final class EditBooking
{
    public function __construct(
        private BookingRepository $bookingRepository,
		private EventRepository $eventRepository,
        private PrepareBookingTicketLimits $prepareBookingTicketLimits,
		private FormRepository $formRepository,
		private GatewayRepository $gatewayRepository,
		private Clock $clock,
    ) {
    }

    public function execute(string $uuid, UserContext $userContext): ?EditBookingResponse
    {
        $booking = $this->bookingRepository->findByReference($uuid);

		if ($booking === null) {
            throw(new \DomainException("Booking with reference {$uuid} not found"));
        }

		$event = $this->eventRepository->find($booking->eventId);

        if($event === null) {
			throw(new \DomainException("Event with ID {$booking->eventId->toInt()} not found"));
		}

		$now = $this->clock->now();

        $ticketBookingsMap = $this->bookingRepository->getTicketBookingsForEvent($event->id);
        $tickets = $event->getAvailableTickets($now, $ticketBookingsMap) ?? new TicketCollection();
        $ticketDtos = $this->prepareBookingTicketLimits->map($tickets, $ticketBookingsMap, $event->overallCapacity);
        $bookingForm = $this->formRepository->find($event->forms->bookingForm);
        $attendeeForm = $this->formRepository->find($event->forms->attendeeForm);
        $availableGateways = $this->gatewayRepository->findActive();
        return EditBookingResponse::from($booking, $event, $bookingForm, $attendeeForm, $availableGateways, $ticketDtos);
    }
}
