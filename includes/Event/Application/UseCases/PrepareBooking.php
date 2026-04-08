<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Application\UseCases;

use Contexis\Events\Booking\Application\Contracts\BookingOptions;
use Contexis\Events\Booking\Application\Services\IssueBookingToken;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Event\Application\DTOs\PrepareBookingResponse;
use Contexis\Events\Event\Application\Service\EventPolicy;
use Contexis\Events\Event\Application\Service\PrepareBookingTicketLimits;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\TicketCollection;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Payment\Domain\GatewayRepository;
use Contexis\Events\Shared\Application\ValueObjects\UserContext;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use DomainException;

final class PrepareBooking
{
    public function __construct(
        private EventRepository $eventRepository,
        private BookingRepository $bookingRepository,
        private GatewayRepository $gatewayRepository,
        private EventPolicy $eventPolicy,
        private \Contexis\Events\Form\Domain\FormRepository $formRepository,
        private IssueBookingToken $issueBookingToken,
        private PrepareBookingTicketLimits $prepareBookingTicketLimits,
        private BookingOptions $bookingOptions,
        private Clock $clock,
    ) {
    }

    public function execute(int $id, UserContext $userContext): ?PrepareBookingResponse
    {
        $now = $this->clock->now();
        $eventId = EventId::from($id);
        $event = $this->eventRepository->find($eventId);

        if ($event === null) {
            return null;
        }

        if (!$this->eventPolicy->userCanView($event, $userContext)) {
            throw new DomainException("User cannot view event {$id}");
        }

        $ticketBookingsMap = $this->bookingRepository->getTicketBookingsForEvent($eventId);

        $tickets = $event->getAvailableTickets($now, $ticketBookingsMap) ?? TicketCollection::empty();
        $ticketDtos = $this->prepareBookingTicketLimits->map($tickets, $ticketBookingsMap, $event->overallCapacity);
        $bookingForm = $this->formRepository->find($event->forms->bookingForm);
        $attendeeForm = $this->formRepository->find($event->forms->attendeeForm);
        $availableGateways = $this->gatewayRepository->findActive();

		if($availableGateways->isEmpty() && $tickets->getLowestAvailablePrice($now)?->toInt() > 0) {
			throw new DomainException("No active payment gateways available");
		}
		
        $token = $this->issueBookingToken->perform($eventId);

        return PrepareBookingResponse::from(
            eventName: $event->name,
            eventStartDate: $event->startDate,
            eventEndDate: $event->endDate,
            eventDescription: $event->description ?? '',
            tickets: $ticketDtos,
            gateways: $availableGateways,
            bookingForm: $bookingForm,
            attendeeForm: $attendeeForm,
            couponsEnabled: $event->allowsCoupons(),
			donationEnabled: $event->donationEnabled,
			donationAdvertisement: $event->donationEnabled ? $this->bookingOptions->donationAdvertisement() : null,
            token: $token,
        );
    }
}
