<?php
declare(strict_types=1);

namespace Contexis\Events\Platform\Wordpress;

use Contexis\Events\Shared\Infrastructure\Contracts\Registrar;

class PostTypeRegistrar implements Registrar
{

    public function __construct(private readonly iterable $post_types)
    {
    }

    public function hook(): void
    {
        add_action('init', function () {
            foreach ($this->post_types as $post_type) {
                $post_type->register();
            }
        }, 9);
    }
}
