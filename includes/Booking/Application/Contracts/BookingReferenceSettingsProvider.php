<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Application\Contracts;

use Contexis\Events\Booking\Application\DTOs\BookingReferenceSettings;
use Contexis\Events\Event\Domain\ValueObjects\EventId;

interface BookingReferenceSettingsProvider
{
    public function forEvent(EventId $eventId): BookingReferenceSettings;
}
