<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Presentation\Resources;

use Contexis\Events\Communication\Application\DTOs\BookingEmailResult;
use Contexis\Events\Communication\Application\DTOs\BookingEmailDeliveryResult;
use Contexis\Events\Communication\Domain\Enums\EmailTarget;
use Contexis\Events\Payment\Domain\Transaction;
use Contexis\Events\Shared\Presentation\Contracts\Resource;

final readonly class BookingCreatedResource implements Resource
{
	public function __construct(
		public string $reference,
		public PaymentBankTransferResource|PaymentRedirectResource|null $payment,
		public string $customerEmailStatus,
	) {
	}

	public static function from(
		string $reference,
		?Transaction $transaction,
		BookingEmailResult $emailResult,
	): self
	{
		if ($transaction === null) {
			return new self(
				reference: $reference,
				payment: null,
				customerEmailStatus: self::resolveCustomerEmailStatus($emailResult),
			);
		}

		if ($transaction->checkoutUrl !== null) {
			return new self(
				reference: $reference,
				payment: PaymentRedirectResource::fromTransaction($transaction),
				customerEmailStatus: self::resolveCustomerEmailStatus($emailResult),
			);
		}

		return new self(
			reference: $reference,
			payment: PaymentBankTransferResource::fromTransaction($transaction),
			customerEmailStatus: self::resolveCustomerEmailStatus($emailResult),
		);
	}

	private static function resolveCustomerEmailStatus(BookingEmailResult $emailResult): string
	{
		$customerDeliveries = array_values(array_filter(
			$emailResult->deliveries,
			static fn ($delivery): bool =>
				$delivery instanceof \Contexis\Events\Communication\Application\DTOs\BookingEmailDeliveryResult
				&& $delivery->target === EmailTarget::CUSTOMER,
		));

		if ($customerDeliveries === []) {
			return 'unknown';
		}

		foreach ($customerDeliveries as $delivery) {
			if ($delivery->status === BookingEmailDeliveryResult::STATUS_SENT) {
				return 'sent';
			}
		}

		foreach ($customerDeliveries as $delivery) {
			if ($delivery->status === BookingEmailDeliveryResult::STATUS_FAILED) {
				return 'failed';
			}
		}

		return 'skipped';
	}

	public function jsonSerialize(): array
	{
		return [
			'reference' => $this->reference,
			'payment' => $this->payment,
			'customerEmailStatus' => $this->customerEmailStatus,
		];
	}
}
