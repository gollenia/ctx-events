<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Domain;

use Contexis\Events\Shared\Domain\Abstract\Collection;

final readonly class GatewayCollection extends Collection
{
    public static function from(PaymentGateway ...$gateways): self
    {
        return new self($gateways);
    }
}
