<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application;

use Contexis\Events\Booking\Domain\ValueObjects\LogEntry;
use Contexis\Events\Booking\Domain\ValueObjects\LogEntryCollection;
use Contexis\Events\Booking\Domain\Enums\BookingLogEvent;
use Contexis\Events\Booking\Domain\Enums\BookingLogLevel;
use Contexis\Events\Communication\Application\DTOs\BookingEmailDeliveryResult;
use Contexis\Events\Communication\Application\DTOs\BookingEmailResult;
use Contexis\Events\Communication\Domain\Enums\EmailTarget;
use Contexis\Events\Shared\Domain\ValueObjects\Actor;

final class BookingEmailWarnings
{
    /** @return list<string> */
    public static function messages(BookingEmailResult $result): array
    {
        $messages = [];

        foreach ($result->deliveries as $delivery) {
            $message = self::messageFor($delivery);

            if ($message === null) {
                continue;
            }

            $messages[] = $message;
        }

        return array_values(array_unique($messages));
    }

    public static function appendToLogEntries(
        LogEntryCollection $logEntries,
        BookingEmailResult $result,
        \DateTimeImmutable $timestamp,
    ): LogEntryCollection {
        foreach (self::messages($result) as $message) {
            $logEntries = $logEntries->add(new LogEntry(
                eventType: BookingLogEvent::EmailWarning,
                level: BookingLogLevel::Warning,
                actor: Actor::system('Email system'),
                timestamp: $timestamp,
                message: $message,
            ));
        }

        return $logEntries;
    }

    private static function messageFor(BookingEmailDeliveryResult $delivery): ?string
    {
        if ($delivery->status === BookingEmailDeliveryResult::STATUS_SENT) {
            return null;
        }

        if ($delivery->reason === 'template_disabled') {
            return null;
        }

        $target = match ($delivery->target) {
            EmailTarget::CUSTOMER => 'customer',
            EmailTarget::ADMIN => 'admin',
            EmailTarget::BILLING_CONTACT => 'billing contact',
            EmailTarget::EVENT_CONTACT => 'event contact',
        };

        $reason = match ($delivery->reason) {
            'recipient_not_resolved' => 'recipient could not be resolved',
            'context_not_found' => 'booking context could not be loaded',
            'send_failed' => 'mail transport rejected the message',
            'send_exception' => 'mail transport raised an exception',
            default => $delivery->reason ?? 'mail could not be sent',
        };

        return sprintf('Email warning for %s: %s.', $target, $reason);
    }
}
