<?php

namespace Contexis\Events\Infrastructure\Persistence;
use WP_Query;
use WP_Post;

abstract class WpAbstractRepository {

	const POST_TYPE_CLASS = "";
	protected array $query = null;


	public function post_to_array(WP_Post $event, bool $include_meta = false) : array {
		$data = $event->to_array();

		if ($include_meta) {
			$data['meta'] = get_post_meta($event->ID);
		}

		return $data;
	}

	public function find(int $id): ?object {
		$this->wp_query = new WP_Query([
			'p' => $id,
			'post_type' => self::POST_TYPE_CLASS::POST_TYPE,
			'posts_per_page' => 1
		]);
	}

	public function order_by($orderBy) {
		$this->wp_query->orderby = $orderBy;
	}

	public function order($order) {
		$this->wp_query->order = $order['order'] ?? 'ASC';
	}

}