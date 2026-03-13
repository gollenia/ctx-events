<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Presentation\Resources;

use Contexis\Events\Payment\Domain\Transaction;
use Contexis\Events\Shared\Presentation\Contracts\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript (name: 'BankTransferPayment')]
final readonly class PaymentBankTransferResource implements Resource
{
    public function __construct(
        public string $gateway,
        public int $status,
        public array $amount,
        public array $bankData,
        public string $instructions,
    ) {
    }

	public static function fromTransaction(Transaction $transaction): self
	{
		if ($transaction->bankData === null) {
			throw new \InvalidArgumentException('Transaction does not contain bank data.');
		}

		return new self(
			gateway: $transaction->gateway,
			status: $transaction->status->value,
			amount: $transaction->amount->toArray(),
			bankData: $transaction->bankData->toArray(),
			instructions: $transaction->instructions,
		);
	}

	public function jsonSerialize(): array
	{
		return [
			'gateway' => $this->gateway,
			'status' => $this->status,
			'amount' => $this->amount,
			'bankData' => $this->bankData,
			'instructions' => $this->instructions,
		];
	}
}