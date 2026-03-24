<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\Dtos;

final readonly class PaymentQrResponse
{
    public function __construct(
        public string $gateway,
        public string $format,
        public string $mimeType,
        public string $dataUri,
    ) {
    }

    public static function from(
        string $gateway,
        string $format,
        string $mimeType,
        string $dataUri
    ): self
    {
        return new self($gateway, $format, $mimeType, $dataUri);
    }
}
