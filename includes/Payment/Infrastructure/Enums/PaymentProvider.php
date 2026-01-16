<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure\Enums;

use Contexis\Events\Payment\Infrastructure\Gateways\Mollie\MollieGateway;
use Contexis\Events\Payment\Infrastructure\Gateways\Offline\OfflineGateway;

enum PaymentProvider: string
{
    case OFFLINE = 'offline';
    case MOLLIE = 'mollie';

	public function getGatewayClass(): string
    {
        return match($this) {
            self::OFFLINE => OfflineGateway::class,
            self::MOLLIE  => MollieGateway::class,
        };
    }
}	