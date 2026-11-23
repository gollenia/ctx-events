<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Presentation\Resources;

use Contexis\Events\Booking\Domain\Enums\BookingLogEvent;
use Contexis\Events\Booking\Domain\ValueObjects\LogEntry;
use Contexis\Events\Shared\Presentation\Contracts\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final readonly class BookingLogEntryResource implements Resource
{
    public function __construct(
        public string $eventType,
        public string $level,
        public string $timestamp,
        public int $actorId,
        public string $actorName,
        public ?string $message,
    ) {
    }

    public static function fromLogEntry(LogEntry $entry): self
    {
        return new self(
            eventType: $entry->eventType->value,
            level: $entry->level->value,
            timestamp: $entry->timestamp->format(DATE_ATOM),
            actorId: $entry->actor->id,
            actorName: $entry->actor->name,
            message: self::translateMessage($entry),
        );
    }

    private static function translateMessage(LogEntry $entry): ?string
    {
        $message = $entry->message;
        if ($message === null) {
            return null;
        }

        if ($entry->eventType === BookingLogEvent::EmailWarning) {
            return self::translateEmailWarning($message);
        }

        // Stored messages contain the formatted English text, including in older bookings.
        return match (true) {
            preg_match('/\AAttendee added for ticket (.+)\.\z/', $message, $parts) === 1 =>
                sprintf(__('Attendee added for ticket %s.', 'ctx-events'), $parts[1]),
            preg_match('/\AAttendee (\d+) moved from ticket (.+) to (.+)\.\z/', $message, $parts) === 1 =>
                sprintf(__('Attendee %1$s moved from ticket %2$s to %3$s.', 'ctx-events'), $parts[1], $parts[2], $parts[3]),
            preg_match('/\AAttendee (\d+) updated\.\z/', $message, $parts) === 1 =>
                sprintf(__('Attendee %s updated.', 'ctx-events'), $parts[1]),
            preg_match('/\AAttendee (\d+) cancelled\z/', $message, $parts) === 1 =>
                sprintf(__('Attendee %s cancelled', 'ctx-events'), $parts[1]),
            preg_match('/\AThe booking total was reduced\. Refund (\d+[.,]\d{2}) externally\.\z/', $message, $parts) === 1 =>
                sprintf(__('The booking total was reduced. Refund %s externally.', 'ctx-events'), self::formatAmount($parts[1])),
            preg_match('/\AOpen payment of (\d+[.,]\d{2}) created after booking update\.\z/', $message, $parts) === 1 =>
                sprintf(__('Open payment of %s created after booking update.', 'ctx-events'), self::formatAmount($parts[1])),
            default => $message,
        };
    }

    private static function formatAmount(string $amount): string
    {
        return number_format_i18n((float) str_replace(',', '.', $amount), 2);
    }

    private static function translateEmailWarning(string $message): string
    {
        if (preg_match('/\AEmail warning for ([^:]+): (.+)\.\z/s', $message, $parts) !== 1) {
            return $message;
        }

        $target = match ($parts[1]) {
            'customer' => _x('customer', 'booking email recipient', 'ctx-events'),
            'admin' => _x('admin', 'booking email recipient', 'ctx-events'),
            'billing contact' => _x('billing contact', 'booking email recipient', 'ctx-events'),
            'event contact' => _x('event contact', 'booking email recipient', 'ctx-events'),
            default => $parts[1],
        };
        $reason = match ($parts[2]) {
            'recipient could not be resolved', 'no_event_contact' => __('recipient could not be resolved', 'ctx-events'),
            'booking context could not be loaded' => __('booking context could not be loaded', 'ctx-events'),
            'mail transport rejected the message' => __('mail transport rejected the message', 'ctx-events'),
            'mail transport raised an exception' => __('mail transport raised an exception', 'ctx-events'),
            'mail could not be sent' => __('mail could not be sent', 'ctx-events'),
            default => $parts[2],
        };

        return sprintf(__('Email warning for %1$s: %2$s.', 'ctx-events'), $target, $reason);
    }

    /** @return array{eventType: string, level: string, timestamp: string, actorId: int, actorName: string, message: ?string} */
    public function jsonSerialize(): array
    {
        return [
            'eventType' => $this->eventType,
            'level' => $this->level,
            'timestamp' => $this->timestamp,
            'actorId' => $this->actorId,
            'actorName' => $this->actorName,
            'message' => $this->message,
        ];
    }
}
