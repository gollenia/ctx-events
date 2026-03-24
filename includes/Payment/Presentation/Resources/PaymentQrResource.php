<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Presentation\Resources;

use Contexis\Events\Payment\Application\Dtos\PaymentQrResponse;
use Contexis\Events\Shared\Presentation\Contracts\Resource;

final readonly class PaymentQrResource implements Resource
{
    public function __construct(
        public string $gateway,
        public string $format,
        public string $mimeType,
        public string $dataUri,
    ) {
    }

    public static function fromDto(PaymentQrResponse $response): self
    {
        return new self(
            gateway: $response->gateway,
            format: $response->format,
            mimeType: $response->mimeType,
            dataUri: $response->dataUri,
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'gateway' => $this->gateway,
            'format' => $this->format,
            'mimeType' => $this->mimeType,
            'dataUri' => $this->dataUri,
        ];
    }
}
