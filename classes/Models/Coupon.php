<?php

namespace Contexis\Events\Models;

use Contexis\Events\Core\Utilities\ValidationResult;
use Contexis\Events\PostTypes\CouponPost;
use Contexis\Events\PostTypes\EventPost;
use DateTime;
use WP_Query;
use WP_User;

class Coupon implements \JsonSerializable{

	const PERCENT = 'percent';
	const FIXED = 'fixed';
	
	public int $id = 0;
	public int $owner = 0;
	public string $code = '';
	public string $name = '';
	public string $description = '';
	public ?DateTime $end = null;
	public int $limit = 0;
	public string $type = '';
	public float $discount = 0;
	public int $used = 0;
	public string $status = '';
	public bool $global = false;

	public static function get_by_id(int $coupon_id) : ?Coupon
	{
		$post = get_post($coupon_id);
		if (empty($post) || $post->post_type !== CouponPost::POST_TYPE) {
			return null;
		}
		$instance = new self();
		return $instance->load_post($post) ? $instance : null;
	}

	public static function get_by_code(string $code) : ?Coupon
	{
		$args = [
			'post_type' => CouponPost::POST_TYPE,
			'meta_query' => [
				[
					'key' => '_coupon_code',
					'value' => $code,
					'compare' => '='
				]
			],
			'posts_per_page' => 1,
			'post_status' => 'publish'
		];
		$query = new WP_Query($args);
		if (empty($query->posts)) {
			return null;
		}
		$post = $query->posts[0];
		
		$instance = new self();
		return $instance->load_post($post) ? $instance : null;
	}

	public static function get_by_post($post) : ?Coupon
	{
		if (empty($post) || $post->post_type !== CouponPost::POST_TYPE) {
			return null;
		}
		$instance = new self();
		return $instance->load_post($post) ? $instance : null;
	}	

	private function load_post($post) : bool
	{
		if ($post->post_type !== CouponPost::POST_TYPE) {
			return false;
		}

		$meta = get_post_meta($post->ID);
		$get = fn($key) => $meta[$key][0] ?? null;

		$this->id = $post->ID;
		$this->owner = $post->post_author;
		$this->name = $post->post_title;
		$this->code = (string) $get('_coupon_code');
		$this->discount = (float) $get('_coupon_value');
		$this->limit = (int) $get('_coupon_limit');
		$this->used = (int) $get('_coupon_used');
		$this->status = (string) $get('_coupon_status');
		$this->global = (bool) $get('_coupon_global');
		$this->description = (string) $get('coupon_description');

		$this->end = $get('_coupon_expiry') ? new \DateTime($get('_coupon_expiry')) : null;

		$type = (string) $get('_coupon_type');
		$this->type = match($type) {
			self::FIXED => self::FIXED,
			default => self::PERCENT
		};
		return true;
	}

	function apply_discount($price) : float {
		switch($this->type){
			case self::PERCENT:
				$price -= $price * ($this->discount / 100);
				break;
			case self::FIXED :
				$price -= $this->discount;
				if( $price < 0 ) $price = 0;
				break;
		}
		return apply_filters('em_coupon_apply_discount', $price, $this);
	}
	
	function get_discount($price) : float
	{
		return $price - $this->apply_discount($price);
	}

	public function increment_used(): void
	{
		$this->used++;
		update_post_meta($this->id, '_coupon_used', $this->used);
	}

	function get_person() : ?WP_User {
		if (!is_numeric($this->owner)) {
			return null;
		}
		return get_userdata($this->owner);
	}

	function jsonSerialize() : array
	{
		return [
			'label' => $this->name,
			'code' => $this->code,
			'discount' => $this->discount,
			'type' => $this->type,
			'description' => $this->description,
			'end' => $this->end ? $this->end->format('Y-m-d') : null,
			'limit' => $this->limit,
			'used' => $this->used,
			'status' => $this->status,
			'global' => $this->global
		];
	}
	

	function validate( Event | null $event ) : ValidationResult
	{

		if (empty($this->code)) {
			return ValidationResult::fail('no_code', __('Coupon code is required.', 'events'));
		}
		if ($this->status === 'disabled') {
			return ValidationResult::fail('coupon_disabled', __('This coupon is disabled.', 'events'));
		}
		
		if ($this->end instanceof DateTime && $this->end < new DateTime()) {
			return ValidationResult::fail('coupon_expired', __('This coupon has expired.', 'events'));
		}

		if (!empty($this->limit) && $this->used >= $this->limit) {
			return ValidationResult::fail('coupon_usage_limit', __('This coupon has reached its usage limit.', 'events'));
		}

		if ($this->global) {
        	return ValidationResult::success();
   	 	}
		
		if (!$event instanceof Event) {
			return ValidationResult::fail('no_event', __('Event is required to validate the coupon.', 'events'));
    	}

	    if (!in_array($this->id, $event->get_coupon_ids(), true)) {
	        return ValidationResult::fail('invalid_event', __('This coupon is not valid for the specified event.', 'events'));
	    }

		return ValidationResult::success();
	}
	

	function get_discount_text() : string 
	{
		return match($this->type) {
			self::PERCENT => sprintf(__('%s off', 'events'), number_format($this->discount, 2) . '%'),
			self::FIXED => sprintf(__('%s off', 'events'), \Contexis\Events\Intl\Price::format($this->discount)),
		};
	}
	
	function has_events() : bool
	{
		$args = [
			'post_type' => EventPost::POST_TYPE,
			'meta_query' => [
				[
					'key' => '_event_coupons',
					'value' => $this->id,
					'compare' => 'LIKE'
				]
			],
			'posts_per_page' => 1,
			'post_status' => 'publish'
		];
		
		$query = new WP_Query($args);
		return !empty($query->posts);
	}

}