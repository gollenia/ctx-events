<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Presentation\Resources;

use Contexis\Events\Event\Application\DTOs\PrepareBookingResponse;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'BookingInfo')]
final class PrepareBookingResource
{
	public function __construct(
		public string $eventName,
		public string $eventStartDate,
		public string $eventEndDate,
		public string $eventDescription,
		public array $tickets,
		public array $gateways,
		public array $bookingForm,
		public ?array $attendeeForm,
		public bool $couponsEnabled,
		public string $token,
	) {
	}

	public static function fromResponse(PrepareBookingResponse $response): self
	{
		return new self(
			eventName: $response->eventName,
			eventStartDate: $response->eventStartDate->format('Y-m-d H:i:s'),
			eventEndDate: $response->eventEndDate->format('Y-m-d H:i:s'),
			eventDescription: $response->eventDescription,
			tickets: array_map(fn($ticket) => $ticket->toArray(), $response->tickets->toArray()),
			gateways: array_map(fn($gateway) => [
				'id'    => $gateway->getId(),
				'title' => $gateway->getTitle(),
				'description' => $gateway->getDescription(),
			], $response->gateways->toArray()),
			bookingForm: $response->bookingForm->toArray(),
			attendeeForm: $response->attendeeForm?->toArray(),
			couponsEnabled: $response->couponsEnabled,
			token: $response->token,
		);
	}

	public function toArray(): array
	{
		return [
			'eventName' => $this->eventName,
			'eventStartDate' => $this->eventStartDate,
			'eventEndDate' => $this->eventEndDate,
			'eventDescription' => $this->eventDescription,
			'tickets' => $this->tickets,
			'gateways' => $this->gateways,
			'bookingForm' => $this->bookingForm,
			'attendeeForm' => $this->attendeeForm,
			'couponsEnabled' => $this->couponsEnabled,
			'token' => $this->token,
		];
	}
}