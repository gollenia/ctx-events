<?php

declare(strict_types=1);

namespace Contexis\Events\Platform\Wordpress;

use Contexis\Events\Shared\Infrastructure\Contracts\Registrar;

final class GatewayRegistrar implements Registrar
{
	/**
	 * @param iterable<object> $gateways
	 */
    public function __construct(private readonly iterable $gateways)
    {
    }

    public function hook(): void
    {
        add_action('plugins_loaded', function () {
            foreach ($this->gateways as $gateway) {
                $gateway->register();
            }
        });
    }
}