<?php

namespace Contexis\Events\Infrastructure\PostTypes;

class PostTypeRegistrar {

	private array $post_types;

	public function __construct(array $post_types) {
		$this->post_types = $post_types;
	}

	public function hook(): void {
        add_action('init', function () {
            foreach ($this->post_types as $cls) {
                method_exists($cls,'register_post_type')  && $cls::register_post_type();
                method_exists($cls,'register_taxonomies') && $cls::register_taxonomies();
                method_exists($cls,'register_meta')       && $cls::register_meta();
            }
        }, 9);
    }

	public function register_post_types(): void {
		foreach ($this->post_types as $post_type) {
			if (class_exists($post_type)) {
				$post_type_instance = new $post_type();
				$post_type_instance->register();
			}
		}
	}
}
