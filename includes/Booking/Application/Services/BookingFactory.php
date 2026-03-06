<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\Services;

use Contexis\Events\Booking\Application\DTOs\CreateBookingRequest;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Booking\Infrastructure\BookingReferenceGenerator;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;

final readonly class BookingFactory
{
    public function __construct(
        private BookingReferenceGenerator $referenceGenerator,
        private AttendeeFactory $attendeeFactory,
        private Clock $clock,
    ) {}

    public function createPending(Event $event, CreateBookingRequest $request): Booking
    {
        $registration = $request->registration; // array

        $email = $this->extractEmail($registration);
        $name  = $this->extractName($registration);

        $attendeesPayload = $request->attendees;
        if ($attendeesPayload === []) {
            throw new \DomainException('At least one attendee is required.');
        }

        $attendees = $this->attendeeFactory->fromPayload($attendeesPayload);

        // After extraction, registration contains only extras
        $extras = new RegistrationData($registration);

        return Booking::createPending(
            reference: $this->referenceGenerator->generate(),
            email: $email,
            name: $name,
            registration: $extras,
            attendees: $attendees,
            bookingTime: $this->clock->now(),
			priceSummary: PriceSummary::calculate($attendees, $request->coupon_code, $event->getCurrency()),
            gateway: $request->gateway,          // string|null (oder VO)
            couponCode: $request->coupon_code,   // string|null (oder VO)
            eventId: $event->id,
        );
    }

    /** @param array<string,mixed> $registration */
    private function extractEmail(array &$registration): Email
    {
        $emailStr = $this->string($registration, 'email');
        if ($emailStr === null || $emailStr === '') {
            throw new \DomainException('Email is required.');
        }

        $email = Email::tryFrom($emailStr);
        if ($email === null) {
            throw new \DomainException('Invalid email format.');
        }

        unset($registration['email']);
        return $email;
    }

    /** @param array<string,mixed> $registration */
    private function extractName(array &$registration): PersonName
    {
        $first = $this->string($registration, 'first_name');
        $last  = $this->string($registration, 'last_name');

        if ($first === null || $last === null || $first === '' || $last === '') {
            throw new \DomainException('First name and last name are required.');
        }

        unset($registration['first_name'], $registration['last_name']);
        return new PersonName($first, $last);
    }

    /** @param array<string,mixed> $data */
    private function string(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;
        if ($value === null) return null;
        if (is_string($value)) return trim($value);
        if (is_scalar($value)) return trim((string) $value);
        return null;
    }
}