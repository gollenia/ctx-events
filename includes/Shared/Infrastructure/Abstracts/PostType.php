<?php

namespace Contexis\Events\Shared\Infrastructure\Abstracts;

abstract class PostType
{
    public const POST_TYPE = '';

    public function register(): static
    {
        $instance = new static();
        add_action('init', [$instance, 'registerPostType']);
		if(method_exists($instance, 'registerTaxonomies')) {
            add_action('init', [$instance, 'registerTaxonomies']);
        }
		if(method_exists($instance, 'registerMeta')) {
            add_action('init', [$instance, 'registerMeta']);
        }
        return $instance;
    }

    abstract public function registerPostType(): void;
}
