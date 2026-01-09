<?php
declare(strict_types=1);

namespace Contexis\Events\Platform\Wordpress;

use Contexis\Events\Shared\Infrastructure\Contracts\Registrar;

final class RestRegistrar implements Registrar
{
    /**
     * @param iterable<RestController> $adapters
     */
    public function __construct(
		private readonly iterable $adapters
	)
    {}	

    public function hook(): void
    {
        add_action('rest_api_init', function () {
            foreach ($this->adapters as $adapter) {
                $adapter->register();
            }
        });
    }
}
