<?php

namespace Contexis\Events\Infrastructure\PostTypes;

abstract class AbstractPostType
{
    public const POST_TYPE = '';

    public function register(): static
    {
        $instance = new static();
        add_action('init', [$instance, 'registerPostType']);
        if (in_array('Contexis\Events\Core\Contracts\HasTaxonomies', class_implements($instance))) {
                add_action('init', [$instance, 'registerTaxonomies']);
        }
        if (in_array('Contexis\Events\Core\Contracts\HasMetaData', class_implements($instance))) {
            add_action('init', [$instance, 'registerMeta']);
        }
        return $instance;
    }

    abstract public function registerPostType(): void;
}
