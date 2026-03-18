<?php
declare(strict_types=1);

namespace Contexis\Events\Platform\Wordpress;

use Contexis\Events\Shared\Infrastructure\Contracts\Registrar;
use Contexis\Events\Shared\Infrastructure\Abstracts\PostType;

class PostTypeRegistrar implements Registrar
{
	/** @param iterable<PostType> $post_types */
    public function __construct(private readonly iterable $post_types)
    {
    }

    public function hook(): void
    {
        foreach ($this->post_types as $post_type) {
            $post_type->register();
        }
    }
}
