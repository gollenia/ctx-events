<?php
declare(strict_types=1);

namespace Contexis\Events\Platform\Wordpress;

use Contexis\Events\Shared\Infrastructure\Contracts\Registrar;

class PostTypeRegistrar implements Registrar
{
    private array $post_types;

    public function __construct(array $post_types)
    {
        $this->post_types = $post_types;
    }

    public function hook(): void
    {
        add_action('init', function () {
            foreach ($this->post_types as $cls) {
                $post_type_instance = new $cls();
                method_exists($post_type_instance, 'registerPostType')  && $post_type_instance->registerPostType();
                method_exists($post_type_instance, 'registerTaxonomies') && $post_type_instance->registerTaxonomies();
                method_exists($post_type_instance, 'registerMeta')       && $post_type_instance->registerMeta();
            }
        }, 9);
    }

    public function registerPostTypes(): void
    {
        foreach ($this->post_types as $post_type) {
            if (class_exists($post_type)) {
                $post_type_instance = new $post_type();
                $post_type_instance->register();
            }
        }
    }
}
