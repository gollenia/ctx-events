<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Presentation\Resources;

use Contexis\Events\Payment\Domain\Transaction;
use Contexis\Events\Shared\Presentation\Contracts\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'RedirectPayment')]
final readonly class PaymentRedirectResource implements Resource
{
    public function __construct(
        public string $gateway,
        public int $status,
        public array $amount,
        public string $externalId,
        public string $checkoutUrl,
        public ?string $gatewayUrl,
        public string $instructions,
    ) {
    }

	public static function fromTransaction(Transaction $transaction): self
	{
		if ($transaction->checkoutUrl !== null) {
			return new self(
				gateway: $transaction->gateway,
				status: $transaction->status->value,
				amount: $transaction->amount->toArray(),
				externalId: $transaction->externalId,
				checkoutUrl: $transaction->checkoutUrl->toString(),
				gatewayUrl: $transaction->gatewayUrl ? $transaction->gatewayUrl->toString() : null,
				instructions: $transaction->instructions,
			);
		}

		return new self(
			gateway: $transaction->gateway,
			status: $transaction->status->value,
			amount: $transaction->amount->toArray(),
			externalId: $transaction->externalId,
			checkoutUrl: '',
			gatewayUrl: null,
			instructions: $transaction->instructions,
		);
	}

	public function jsonSerialize(): array
	{
		return [
			'gateway' => $this->gateway,
			'status' => $this->status,
			'amount' => $this->amount,
			'externalId' => $this->externalId,
			'checkoutUrl' => $this->checkoutUrl,
			'gatewayUrl' => $this->gatewayUrl,
			'instructions' => $this->instructions,
		];
	}
}
