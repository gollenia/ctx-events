<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Application\UseCases;

use Contexis\Events\Booking\Application\DTOs\CreateBookingRequest;
use Contexis\Events\Booking\Application\Services\AttendeeFactory;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\AttendeeRepository;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\BookingTokenStore;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Booking\Infrastructure\BookingReferenceGenerator;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\TicketCollection;
use Contexis\Events\Payment\Domain\GatewayRepository;
use Contexis\Events\Payment\Domain\TransactionRepository;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\Contracts\SessionHashResolver;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;

final class CreateBooking
{
    public function __construct(
        private BookingRepository $bookingRepository,
        private AttendeeRepository $attendeeRepository,
        private EventRepository $eventRepository,
        private GatewayRepository $gatewayRepository,
        private TransactionRepository $transactionRepository,
        private BookingTokenStore $tokenStore,
        private BookingReferenceGenerator $referenceGenerator,
        private AttendeeFactory $attendeeFactory,
        private SessionHashResolver $sessionHashResolver,
        private Clock $clock,
    ) {}

    public function execute(CreateBookingRequest $request): string
    {
        $this->validateToken($request);

        $now = $this->clock->now();
        $event = $this->eventRepository->find($request->eventId);

        if ($event === null) {
            throw new \DomainException('Event not found.');
        }

        $ticketBookingsMap = $this->bookingRepository->getTicketBookingsForEvent($request->eventId);
        $event = $event->withAvailabilitySnapshot($ticketBookingsMap);
        $decision = $event->canBookAt($now);

        if (!$decision->allowed) {
            throw new \DomainException('Event is not bookable: ' . $decision->reason->name);
        }

        $tickets = $event->tickets ?? new TicketCollection();
        $attendees = $this->attendeeFactory->fromPayload($request->attendees, $tickets);

        $currency = $event->currency?->toString() ?? 'EUR';
        $totalCents = array_sum(
            array_map(fn ($a) => $a->ticketPrice->amountCents, $attendees->toArray())
        );
        $priceSummary = PriceSummary::fromValues($totalCents, 0, 0, $currency);

        $registration = new RegistrationData($request->registration);
        $email = $this->extractEmail($request->registration);
        $name = $this->extractName($request->registration);
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
        );

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

        $this->tokenStore->delete($request->token);

        return $reference->toString();
    }

    private function validateToken(CreateBookingRequest $request): void
    {
        $record = $this->tokenStore->find($request->token);

        if ($record === null) {
            throw new \DomainException('Invalid or expired booking token.');
        }

        if ($record->eventId !== $request->eventId->toInt()) {
            throw new \DomainException('Booking token does not match event.');
        }

        $sessionHash = $this->sessionHashResolver->resolve();
        if ($record->sessionHash !== $sessionHash) {
            throw new \DomainException('Session mismatch. Please reload the page and try again.');
        }
    }

    /** @param array<string, mixed> $registration */
    private function extractEmail(array $registration): Email
    {
        $address = trim((string) ($registration['email'] ?? ''));
        $email = Email::tryFrom($address);

        if ($email === null) {
            throw new \DomainException('A valid email address is required.');
        }

        return $email;
    }

    /** @param array<string, mixed> $registration */
    private function extractName(array $registration): PersonName
    {
        $firstName = trim((string) ($registration['first_name'] ?? ''));
        $lastName = trim((string) ($registration['last_name'] ?? ''));

        if ($firstName === '' || $lastName === '') {
            throw new \DomainException('First name and last name are required.');
        }

        return PersonName::from($firstName, $lastName);
    }
}
