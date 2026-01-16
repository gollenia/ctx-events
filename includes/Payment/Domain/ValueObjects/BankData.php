<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Domain\ValueObjects;

final class BankData implements \JsonSerializable
{
    public function __construct(
        public readonly string $accountHolder,
        public readonly string $iban,
        public readonly string $bic,
        public readonly string $bankName,
        public readonly string $reference
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'accountHolder' => $this->accountHolder,
            'iban' => $this->iban,
            'bic' => $this->bic,
            'bankName' => $this->bankName,
            'reference' => $this->reference,
        ];
    }
}
