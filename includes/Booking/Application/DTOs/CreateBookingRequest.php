<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\DTOs;

use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;

final class CreateBookingRequest
{
	public function __construct(
		public EventId $eventId,
		public array $registration,
		public array $attendees,
		public string $gateway,
		public ?string $token = null,
		public ?string $couponCode = null,
		public ?int $donationAmount = 0,
	) {}
}

