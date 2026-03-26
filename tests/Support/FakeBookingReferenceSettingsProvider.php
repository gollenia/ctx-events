<?php
declare(strict_types=1);

namespace Tests\Support;

use Contexis\Events\Booking\Application\Contracts\BookingReferenceSettingsProvider;
use Contexis\Events\Booking\Application\DTOs\BookingReferenceSettings;
use Contexis\Events\Event\Domain\ValueObjects\EventId;

final class FakeBookingReferenceSettingsProvider implements BookingReferenceSettingsProvider
{
    public function __construct(private readonly BookingReferenceSettings $settings = new BookingReferenceSettings())
    {
    }

    public function forEvent(EventId $eventId): BookingReferenceSettings
    {
        return $this->settings;
    }
}
