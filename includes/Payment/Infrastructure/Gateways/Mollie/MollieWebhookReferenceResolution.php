<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure\Gateways\Mollie;

final readonly class MollieWebhookReferenceResolution
{
    private function __construct(
        public ?string $externalId,
        public bool $shouldIgnore,
    ) {
    }

    public static function sync(string $externalId): self
    {
        return new self($externalId, false);
    }

    public static function ignore(): self
    {
        return new self(null, true);
    }

    public static function invalid(): self
    {
        return new self(null, false);
    }
}
