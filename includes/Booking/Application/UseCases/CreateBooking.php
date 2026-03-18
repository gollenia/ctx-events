<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Application\UseCases;

use Contexis\Events\Booking\Application\DTOs\CreateBookingRequest;
use Contexis\Events\Booking\Application\DTOs\CreateBookingResponse;
use Contexis\Events\Booking\Application\Contracts\ReferenceGenerator;
use Contexis\Events\Booking\Application\Services\AttendeeFactory;
use Contexis\Events\Booking\Application\Services\BookingTokenValidator;
use Contexis\Events\Booking\Domain\Services\CalculateBookingPrice;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\Signals\BookingCreatedOnlineSignal;
use Contexis\Events\Booking\Domain\Signals\BookingPendingManualSignal;
use Contexis\Events\Booking\Domain\Enums\BookingEvent;
use Contexis\Events\Booking\Domain\AttendeeRepository;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\ValueObjects\LogEntry;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Event\Application\Service\CheckTicketAvailibility;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Payment\Domain\Coupon;
use Contexis\Events\Payment\Domain\CouponRepository;
use Contexis\Events\Payment\Domain\GatewayRepository;
use Contexis\Events\Payment\Domain\TransactionRepository;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\Contracts\CurrentActorProvider;
use Contexis\Events\Shared\Domain\Contracts\SignalDispatcher;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\Price;

final class CreateBooking
{
    public function __construct(
        private BookingRepository $bookingRepository,
        private AttendeeRepository $attendeeRepository,
        private EventRepository $eventRepository,
        private GatewayRepository $gatewayRepository,
        private TransactionRepository $transactionRepository,
        private ReferenceGenerator $referenceGenerator,
        private AttendeeFactory $attendeeFactory,
        private Clock $clock,
        private CurrentActorProvider $currentActorProvider,
        private SignalDispatcher $signalDispatcher,
        private CheckTicketAvailibility $checkTicketAvailibility,
		private CalculateBookingPrice $calculateBookingPrice,
		private CouponRepository $couponRepository,
		private BookingTokenValidator $tokenValidator
    ) {}

    public function execute(CreateBookingRequest $request): CreateBookingResponse
    {
		if ($request->token === null) {
			throw new \DomainException('Booking token is required.');
		}

        $this->tokenValidator->perform($request->eventId, $request->token);

        $now = $this->clock->now();
        $event = $this->eventRepository->find($request->eventId);

        if ($event === null) {
            throw new \DomainException('Event not found.');
        }

        $ticketBookingsMap = $this->bookingRepository->getTicketBookingsForEvent($request->eventId);
        $decision = $event->canBookAt($now, $ticketBookingsMap);

        if (!$decision->allowed) {
            throw new \DomainException('Event is not bookable: ' . $decision->reason->name);
        }

        $availableTickets = $event->tickets;
        $attendees = $this->attendeeFactory->fromPayload($request->attendees, $availableTickets);

        $this->checkTicketAvailibility->perform($attendees, $ticketBookingsMap, $availableTickets, $now);

        $currency = $event->currency ?? Currency::fromCode('EUR');
        $coupon = $this->resolveCoupon($request->couponCode);
        $donation = $this->resolveDonation($request->donationAmount, $event->donationEnabled, $currency);

        $priceSummary = $this->calculateBookingPrice->perform(
			availableTickets: $availableTickets,
			coupon: $coupon,
			attendees: $attendees,
			donation: $donation,
			currency: $currency,
		);

        $registration = new RegistrationData($request->registration);
        $email = $registration->requireEmail();
        $name = $registration->requirePersonName();
        $reference = $this->referenceGenerator->create();

        $booking = Booking::createPending(
            reference: $reference,
            email: $email,
            name: $name,
            bookingTime: $now,
            eventId: $request->eventId,
            registration: $registration,
            attendees: $attendees,
            priceSummary: $priceSummary,
            gateway: $request->gateway,
        )->appendLogEntry(new LogEntry(
            eventType: BookingEvent::Created,
            actor: $this->currentActorProvider->current(),
            timestamp: $now,
        ));

        $bookingId = $this->bookingRepository->save($booking);
        $booking = $booking->withId($bookingId);

        $this->attendeeRepository->saveAll($attendees, $bookingId);

        if (!$priceSummary->isFree()) {
            $gateway = $this->gatewayRepository->find($request->gateway);
            if ($gateway === null) {
                throw new \DomainException("Payment gateway not found: {$request->gateway}");
            }
            $transaction = $gateway->initiatePayment($booking);
            $this->transactionRepository->save($transaction);
        }

        $this->signalDispatcher->dispatch(
            $priceSummary->isFree() || $request->gateway === 'offline'
                ? new BookingPendingManualSignal($bookingId)
                : new BookingCreatedOnlineSignal($bookingId)
        );

        return CreateBookingResponse::from($reference, $transaction ?? null);
    }

    private function resolveCoupon(?string $couponCode): ?Coupon
    {
        if ($couponCode === null || $couponCode === '') {
            return null;
        }

        return $this->couponRepository->findByCode($couponCode);
    }

    private function resolveDonation(?int $amountCents, bool $donationEnabled, Currency $currency): ?Price
    {
        if (!$amountCents) {
            return null;
        }

        if (!$donationEnabled) {
            throw new \DomainException('This event does not accept donations.');
        }

        return Price::from($amountCents, $currency);
    }
}
