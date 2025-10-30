<?php

namespace Contexis\Events\Infrastructure\Persistence;

use WP_Post;

abstract class AbstractPostTypeRepository {
	
	protected const POST_TYPE_CLASS = '';
	protected const MODEL_CLASS = '';
	protected const MAPPER_CLASS = '';
	protected const COLLECTION_CLASS = '';

	public function prepare_post_data(WP_Post $post) : array
	{
		$meta = get_post_meta($post->ID);

		$flatMeta = [];
		foreach ($meta as $key => $value) {
			$key = ltrim($key, '_');
			$flatMeta[$key] = $value[0] ?? null;
		}
		return array_merge($post->to_array(), $flatMeta);
	}

	public function get_by_id(int $post_id): ?object {
		$post = get_post($post_id);
		if (empty($post) || $post->post_type !== static::POST_TYPE_CLASS::POST_TYPE) {
			return null;
		}

		$result = $this->prepare_post_data($post);
		$mapper_class = static::MAPPER_CLASS;
		return $mapper_class::map($result);
	}

	public function find(array $args = []): object {
		$default_args = [
			'post_type' => static::POST_TYPE_CLASS::POST_TYPE,
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'meta_query' => []
		];
		$args = array_merge($default_args, $args);
		$query = new \WP_Query($args);
		
		$mapper_class = static::MAPPER_CLASS;
		$collection_class = static::COLLECTION_CLASS;

		if (empty($query->posts)) {
			return new $collection_class(); 
		}

		$models = array_map(function($post) use ($mapper_class) {
			$data = $this->prepare_post_data($post);
			return $mapper_class::map($data);
		}, $query->posts);
		
		return new $collection_class($models);
	}

}