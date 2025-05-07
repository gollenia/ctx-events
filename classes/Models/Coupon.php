<?php

namespace Contexis\Events\Models;

use Contexis\Events\PostTypes\CouponPost;
use Contexis\Events\PostTypes\EventPost;
use DateTime;
use WP_Query;

class Coupon {
	
	public int $coupon_id = 0;
	public int $coupon_owner = 0;
	public string $coupon_code = '';
	public string $coupon_name = '';
	public string $coupon_description = '';
	public ?DateTime $coupon_end = null;
	public int $coupon_limit = 0;
	public string $coupon_type = '';
	public float $coupon_discount = 0;
	public int $coupon_used = 0;
	public string $coupon_status = '';

	const PERCENT = 'percent';
	const FIXED = 'fixed';
	
	
	public static function get_by_id(int $coupon_id) : ?Coupon
	{
		$post = get_post($coupon_id);
		if (empty($post) || $post->post_type !== CouponPost::POST_TYPE) {
			return null;
		}
		$instance = new self();
		return $instance->load_post($post) ? $instance : null;
	}

	public static function get_by_code(string $coupon_code) : ?Coupon
	{
		$args = [
			'post_type' => CouponPost::POST_TYPE,
			'meta_query' => [
				[
					'key' => 'coupon_code',
					'value' => $coupon_code,
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

		$this->coupon_id = $post->ID;
		$this->coupon_owner = $post->post_author;
		$this->coupon_name = $post->post_title;
		$this->coupon_code = (string) $get('_coupon_code');
		$this->coupon_discount = (float) $get('_coupon_value');
		$this->coupon_type = (string) $get('_coupon_type');
		$this->coupon_limit = (int) $get('_coupon_limit');
		$this->coupon_used = (int) $get('_coupon_used');
		$this->coupon_status = (string) $get('_coupon_status');
		
		$this->coupon_description = (string) $get('coupon_description');

		$this->coupon_end = $get('_coupon_expiry') ? new \DateTime($get('_coupon_expiry')) : null;
		return true;
	}

	function apply_discount($price){
		switch($this->coupon_type){
			case '%':
				//discount by percent
				$price -= $price * ($this->coupon_discount / 100);
				break;
			case '#' :
				//discount by price
				$price -= $this->coupon_discount;
				if( $price < 0 ) $price = 0; //no negative values
				break;
		}
		return apply_filters('em_coupon_apply_discount', $price, $this);
	}
	
	function get_discount($price){
		return $price - $this->apply_discount($price);
	}
	
	function get_person() {
		if (!is_numeric($this->coupon_owner)) {
			return null;
		}
		return get_userdata($this->coupon_owner);
	}
	
	/**
	 * Returns boolean depending whether this coupon is valid right now (i.e. meets time/capacity requirements)
	 * @return boolean
	 */
	function is_valid() : bool
	{
	    $valid = true;
		if( $this->coupon_end && $this->coupon_end < new DateTime() ) return false;
		if( !empty($this->coupon_limit) && $this->coupon_used >= $this->coupon_limit ) return false;
		if( $this->coupon_status == 'disabled' ) return false;
		return true;
	}
	

	
	/**
	 * Puts the coupon into a text representation in terms of discount
	 */
	function get_discount_text() : string 
	{
		return match($this) {
			self::PERCENT => sprintf(__('%s off', 'events'), number_format($this->coupon_discount, 2) . '%'),
			self::FIXED => sprintf(__('%s off', 'events'), \Contexis\Events\Intl\Price::format($this->coupon_discount)),
		};
	}
	
	function has_events(){
		$args = [
			'post_type' => EventPost::POST_TYPE,
			'meta_query' => [
				[
					'key' => 'coupon_id',
					'value' => $this->coupon_id,
					'compare' => '='
				]
			],
			'posts_per_page' => 1,
			'post_status' => 'publish'
		];
		
		$query = new WP_Query($args);
		
		return !empty($query->posts);
	}

}