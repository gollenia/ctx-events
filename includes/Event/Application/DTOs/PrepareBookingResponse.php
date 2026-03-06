<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application\DTOs;

use Cassandra\Date;
use Contexis\Events\Form\Domain\Form;
use Contexis\Events\Payment\Domain\GatewayCollection;
use Contexis\Events\Payment\Domain\PaymentGateway;
use DateTimeImmutable;

final readonly class PrepareBookingResponse
{
    public function __construct(
		public string $eventName,
		public DateTimeImmutable $eventStartDate,
		public DateTimeImmutable $eventEndDate,
		public string $eventDescription,
        public TicketResponseCollection $tickets,
        public GatewayCollection $gateways,
        public Form $bookingForm,
        public ?Form $attendeeForm,
        public bool $couponsEnabled,
		public string $token, 
    ) {
    }

    public static function from(
		 string $eventName,
		 DateTimeImmutable $eventStartDate,
		 DateTimeImmutable $eventEndDate,
		 string $eventDescription,
        TicketResponseCollection $tickets,
        GatewayCollection $gateways,
        Form $bookingForm,
        ?Form $attendeeForm,
        bool $couponsEnabled,
        string $token
    ): self {
        return new self(
			eventName: $eventName,
			eventStartDate: $eventStartDate,
			eventEndDate: $eventEndDate,
			eventDescription: $eventDescription,
            tickets: $tickets,
            gateways: $gateways,
            bookingForm: $bookingForm,
            attendeeForm: $attendeeForm,
            couponsEnabled: $couponsEnabled,
			token: $token,
        );
    }

    public function toArray(): array
    {
        return [
            'eventName' => $this->eventName,
            'eventStartDate' => $this->eventStartDate->format(DateTimeImmutable::ATOM),
            'eventEndDate' => $this->eventEndDate->format(DateTimeImmutable::ATOM),
            'eventDescription' => $this->eventDescription,
            'tickets' => $this->tickets->toArray(),
            'gatewaysAvailable' => $this->gateways->toArray(),
            'bookingForm' => $this->bookingForm?->toArray(),
            'attendeeForm' => $this->attendeeForm?->toArray(),
            'hasCoupons' => $this->couponsEnabled,
			'_token' => $this->token, 
        ];
    }
}
