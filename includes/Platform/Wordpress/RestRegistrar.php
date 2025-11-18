<?php

namespace Contexis\Events\Platform\Wordpress;

use Contexis\Events\Shared\Infrastructure\Contracts\Registrar;

final class RestRegistrar implements Registrar
{
    /** @var RestController[] */
    private array $adapters = [];

    public function __construct(array $adapters)
    {
        $this->adapters = $adapters;
    }

    public function hook(): void
    {

        add_action('rest_api_init', function () {
            foreach ($this->adapters as $adapter) {
                if (!$adapter->register()) {
                    throw new \RuntimeException('Failed to register REST adapter: ' . get_class($adapter));
                }
            }
        });
    }
}
