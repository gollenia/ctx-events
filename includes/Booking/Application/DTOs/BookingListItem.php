<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\DTOs;

use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Contexis\Events\Shared\Domain\ValueObjects\Price;

final readonly class BookingListItem
{
    public function __construct(
        public int $id,
        public string $reference,
        public Email $email,
        public PersonName $name,
        public EventId $eventId,
        public string $eventTitle,
        public int $status,
        public PriceSummary $priceSummary,
        public \DateTimeImmutable $bookingTime,
        public int $spaces = 0,
		public ?string $gateway,
        public ?string $gatewayName = null,
    ) {
    }

    public function withGatewayName(string $gatewayName): self
    {
        return clone($this, ['gatewayName' => $gatewayName]);
    }
}
