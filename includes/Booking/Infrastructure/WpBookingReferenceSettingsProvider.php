<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Infrastructure;

use Contexis\Events\Booking\Application\Contracts\BookingReferenceSettingsProvider;
use Contexis\Events\Booking\Application\DTOs\BookingReferenceSettings;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Infrastructure\EventMeta;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;

final class WpBookingReferenceSettingsProvider implements BookingReferenceSettingsProvider
{
    public function forEvent(EventId $eventId): BookingReferenceSettings
    {
        $snapshot = PostSnapshot::fromWpPostId($eventId->toInt());

        if ($snapshot === null) {
            return new BookingReferenceSettings();
        }

        return new BookingReferenceSettings(
            prefix: $snapshot->getString(EventMeta::BOOKING_REFERENCE_PREFIX, '') ?? '',
            suffix: $snapshot->getString(EventMeta::BOOKING_REFERENCE_SUFFIX, '') ?? '',
        );
    }
}
