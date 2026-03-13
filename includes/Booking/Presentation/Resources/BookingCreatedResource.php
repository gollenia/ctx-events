<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Presentation\Resources;

use Contexis\Events\Payment\Domain\Transaction;
use Contexis\Events\Shared\Presentation\Contracts\Resource;
use Mollie\Api\Resources\Payment;

final readonly class BookingCreatedResource implements Resource
{
	public function __construct(
		public string $reference,
		public PaymentBankTransferResource|PaymentRedirectResource|null $payment,
	) {
	}

	public static function from(string $reference, ?Transaction $transaction): self
	{
		if ($transaction === null) {
			return new self(reference: $reference, payment: null);
		}

		if ($transaction->checkoutUrl !== null) {
			return new self(
				reference: $reference,
				payment: PaymentRedirectResource::fromTransaction($transaction)
			);
		}

		return new self(
			reference: $reference,
			payment: PaymentBankTransferResource::fromTransaction($transaction)
		);
	}

	public function jsonSerialize(): array
	{
		return [
			'reference' => $this->reference,
			'payment' => $this->payment,
		];
	}
}