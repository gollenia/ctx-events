<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application\DTOs;

use Contexis\Events\Shared\Domain\ValueObjects\Price;

final readonly class PrepareBookingTicketResponse
{
	public function __construct(
		public string $ticketId,
		public string $ticketName,
		public string $ticketDescription,
		public Price $ticketPrice,
		public int $ticketCapacity,
		public ?bool $ticketEnabled,
		public ?string $ticketSalesStart,
		public ?string $ticketSalesEnd,
		public ?int $ticketOrder,
		public ?int $ticketMin,
		public ?int $ticketMax,
	) {
	}
}