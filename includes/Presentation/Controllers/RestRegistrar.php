<?php

namespace Contexis\Events\Presentation\Controllers;

use Contexis\Events\Core\Contracts\Registrar;

final class RestRegistrar implements Registrar
{
    /** @var RestAdapter[] */
    private array $adapters = [];

    public function __construct(array $adapters)
    {
        $this->adapters = $adapters;
    }

    public function hook(): void
    {

        add_action('rest_api_init', function () {
            foreach ($this->adapters as $adapter) {
                $adapter->register();
            }
        });
    }
}
