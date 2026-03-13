<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\DTOs;

use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;
use Contexis\Events\Payment\Domain\Transaction;

final readonly class CreateBookingResponse
{
	public function __construct(
		public BookingReference $reference,
		public Transaction $transaction
	) {
	}

	public static function from(BookingReference $reference, Transaction $transaction): self
	{
		return new self($reference, $transaction);
	}
}