<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Presentation\Resources;

use Contexis\Events\Payment\Domain\Transaction;
use Contexis\Events\Shared\Presentation\Contracts\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'BookingTransactionResource')]
final readonly class BookingTransactionResource implements Resource
{
    public function __construct(
        public string $gateway,
        public int $status,
        public array $amount,
        public string $externalId,
        public string $createdAt,
        public ?array $bankData,
        public string $instructions,
        public ?string $checkoutUrl,
        public ?string $gatewayUrl,
    ) {
    }

    public static function fromTransaction(Transaction $transaction): self
    {
        return new self(
            gateway: $transaction->gateway,
            status: $transaction->status->value,
            amount: $transaction->amount->toArray(),
            externalId: $transaction->externalId ?? '',
            createdAt: $transaction->createdAt->format(DATE_ATOM),
            bankData: $transaction->bankData?->toArray(),
            instructions: $transaction->instructions,
            checkoutUrl: $transaction->checkoutUrl ? (string) $transaction->checkoutUrl : null,
            gatewayUrl: $transaction->gatewayUrl ? (string) $transaction->gatewayUrl : null,
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'gateway' => $this->gateway,
            'status' => $this->status,
            'amount' => $this->amount,
            'externalId' => $this->externalId,
            'createdAt' => $this->createdAt,
            'bankData' => $this->bankData,
            'instructions' => $this->instructions,
            'checkoutUrl' => $this->checkoutUrl,
            'gatewayUrl' => $this->gatewayUrl,
        ];
    }
}
