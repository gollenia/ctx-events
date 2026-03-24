<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application;

use Contexis\Events\Booking\Application\Contracts\BookingOptions;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Communication\Domain\Enums\EmailTarget;
use Contexis\Events\Communication\Domain\ValueObjects\AdminEmailRecipientConfig;
use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Person\Domain\PersonRepository;
use Contexis\Events\Shared\Domain\ValueObjects\Email;

final readonly class ResolveEmailRecipient
{
    public function __construct(
        private EventRepository $eventRepository,
        private PersonRepository $personRepository,
        private BookingOptions $bookingOptions,
    ) {
    }

    public function execute(EmailTarget $target, Booking $booking, ?Event $event = null): ?Email
    {
        return match ($target) {
            EmailTarget::CUSTOMER => $booking->email,
            EmailTarget::BILLING_CONTACT => Email::tryFrom($booking->registration->getString('billing_email')),
            EmailTarget::EVENT_CONTACT => $this->resolveEventContact($booking, $event),
            EmailTarget::ADMIN => null,
        };
    }

    /**
     * @return list<Email>
     */
    public function executeMany(EmailTarget $target, Booking $booking, ?Event $event = null, ?AdminEmailRecipientConfig $config = null): array
    {
        if ($target !== EmailTarget::ADMIN) {
            $recipient = $this->execute($target, $booking, $event);

            return $recipient instanceof Email ? [$recipient] : [];
        }

        $event ??= $this->eventRepository->find($booking->eventId);
        $config ??= AdminEmailRecipientConfig::defaultsFor($target);
        $recipients = [];

        if ($config->sendToEventContact) {
            $contact = $this->resolveEventContact($booking, $event);
            if ($contact instanceof Email) {
                $recipients[$contact->toString()] = $contact;
            }
        }

        if ($config->sendToEventPerson) {
            $person = $this->resolveEventPerson($event);
            if ($person instanceof Email) {
                $recipients[$person->toString()] = $person;
            }
        }

        if ($config->sendToBookingAdmin) {
            $bookingAdmin = $this->bookingOptions->adminNotificationEmail();
            if ($bookingAdmin instanceof Email) {
                $recipients[$bookingAdmin->toString()] = $bookingAdmin;
            }
        }

        if ($config->sendToWpAdmin) {
            $wpAdmin = function_exists('get_option')
                ? Email::tryFrom((string) \get_option('admin_email', ''))
                : null;
            if ($wpAdmin instanceof Email) {
                $recipients[$wpAdmin->toString()] = $wpAdmin;
            }
        }

        foreach ($config->customRecipients as $customRecipient) {
            $email = Email::tryFrom($customRecipient);
            if ($email instanceof Email) {
                $recipients[$email->toString()] = $email;
            }
        }

        return array_values($recipients);
    }

    private function resolveEventContact(Booking $booking, ?Event $event = null): ?Email
    {
        $event ??= $this->eventRepository->find($booking->eventId);

        if (!$event instanceof Event || $event->personId === null) {
            return null;
        }

        return $this->personRepository->find($event->personId)?->email;
    }

    private function resolveEventPerson(?Event $event): ?Email
    {
        if (!$event instanceof Event || $event->personId === null) {
            return null;
        }

        return $this->personRepository->find($event->personId)?->email;
    }
}
