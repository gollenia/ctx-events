<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application;

use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Communication\Domain\Enums\EmailTarget;
use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Person\Domain\PersonRepository;
use Contexis\Events\Shared\Domain\ValueObjects\Email;

final readonly class ResolveEmailRecipient
{
    public function __construct(
        private EventRepository $eventRepository,
        private PersonRepository $personRepository,
    ) {
    }

    public function execute(EmailTarget $target, Booking $booking): ?Email
    {
        return match ($target) {
            EmailTarget::CUSTOMER => $booking->email,
            EmailTarget::BILLING_CONTACT => Email::tryFrom($booking->registration->getString('billing_email')),
            EmailTarget::EVENT_CONTACT => $this->resolveEventContact($booking),
            EmailTarget::ADMIN => null,
        };
    }

    private function resolveEventContact(Booking $booking): ?Email
    {
        $event = $this->eventRepository->find($booking->eventId);

        if (!$event instanceof Event || $event->personId === null) {
            return null;
        }

        return $this->personRepository->find($event->personId)?->email;
    }
}
