<?php

namespace Contexis\Events\Collections;

use Contexis\Events\Collections\BookingCollection;
use Contexis\Events\Models\Booking;
use Contexis\Events\Models\Coupon;
use Contexis\Events\Models\Event;
use Contexis\Events\PostTypes\CouponPost;
use WP_REST_Request;

class CouponCollection implements \IteratorAggregate, \Countable, \JsonSerializable {

	private array $coupons = [];
     
	public static function init(){
	    
		//add field to booking form and ajax
		$instance = new self;
		
		add_action('rest_api_init', [$instance, 'register_rest_routes']);
		//hook into booking submission to add discount and coupon info
		
		//add ajax response for coupon code queries
		
		//deal with bookings that have coupons when they get deleted or cancelled
		add_filter('em_booking_delete', array($instance,'em_booking_delete'), 10, 2);
		add_filter('em_booking_set_status', array($instance,'em_booking_set_status'), 10, 2);
		add_filter('em_bookings_delete', array($instance,'em_bookings_delete'), 10, 3);
        //show available coupons on event booking admin area
		//placeholders
		//hook into price calculations
		add_filter('em_booking_get_price_adjustments', array($instance, 'em_booking_get_price_adjustments'), 10, 3);
		//add coupon info to CSV
		add_action('em_bookings_table_cols_template', array($instance, 'em_bookings_table_cols_template'),10,1);
		add_filter('em_bookings_table_rows_col', array($instance, 'em_bookings_table_rows_col'), 10, 3);
		//add css for coupon field
	}
	
	public static function em_booking_get_price_adjustments( $adjustments, $type, $booking ){
		if( $type != 'discounts' ) return $adjustments;
		$coupons = self::booking_get_coupons($booking);
		if( is_array($coupons) && count($coupons) > 0 ){
			foreach($coupons as $coupon){ /* @var $coupon EM_Coupon */
				$adjustments[] = array(
					'name' => $coupon->code . ' - '. $coupon->get_discount_text(),
					'type'=> $coupon->type,
					'amount'=> $coupon->discount,
					'desc' => $coupon->name,
				);
			}
		}
	    return $adjustments;
	}

	public function add(Coupon $coupon) : self
	{
		$this->coupons[] = $coupon;
		return $this;
	}
	
	

	public static function from_event(Event $event) : self
	{
		$collection = self::get_global_coupons();
		$ids = $event->get_coupon_ids();
		foreach($ids as $id) {
			$coupon = Coupon::get_by_id($id);
			if ($coupon) {
				$collection->add($coupon);
			}
		}
		return $collection;
	}

	public static function get_global_coupons() : self
	{
		$args = [
			'meta_query' => [
				[
					'key' => '_coupon_global',
					'value' => '',
					'compare' => '='
				]
			]
		];
		
		return self::find($args);
	}
	
	/**
	 * Gets all coupon ids available to an event
	 * @param Event $event
	 * @return array
	 */
	public static function event_get_coupon_ids($event) : self {
	    return self::find(array('event'=>$event->event_id, 'ids'=>true));
	}


	public static function event_has_coupons($event){
	    if(self::event_has_global_coupons($event)) {
	        return true;
	    }

	    $coupon_ids = self::event_get_coupon_ids($event);
	    return !empty($coupon_ids);
	}

	public static function event_has_global_coupons(Event $event) : bool
	{
		$coupons = self::get_global_coupons();
		return $coupons->count() > 0;
	}


	/* Booking Helpers */
	public static function booking_has_coupons($booking){
	    return $booking->coupon_id > 0;
	}
	

	public static function booking_get_coupons(Booking $booking){
	    $coupons = array();
	    if( $booking->coupon_id == 0 ) return $coupons;
	
	    $coupon = Coupon::get_by_id($booking->coupon_id);
	    $coupons[$coupon->id] = $coupon;
	    
	    return $coupons;
	}
	
	/* Multiple Booking Functions */
	
	
	public static function em_booking_save($result, $booking){
		if( $result ){
			self::refresh_counts($booking);
		}
		return apply_filters('em_coupons_em_booking_save', $result, $booking);
	}
	
	
	public static function em_event_delete_meta($result, $event){
		//TODO deleted events should delete coupon references
		global $wpdb;
		if($result){
			$result_coupons = $wpdb->query("DELETE FROM ".EM_META_TABLE." WHERE meta_key='event-coupon' AND object_id=".$event->event_id);
		}
		return $result && $result_coupons !== false;
	}
	

	public static function em_booking_delete($result, $booking) : bool 
	{
		if( $result ){
			self::refresh_counts($booking);
		}
		return $result;
	}
	
	public static function em_bookings_delete(bool $result, array $booking_ids, BookingCollection $booking_collection){
		/* @todo when coupon-booking relations are stored, use $booking_ids instead. */
		if( $result ){
			foreach( $booking_collection as $booking ){
				self::em_booking_delete($result, $booking);
			}
		}
		return $result;
	}
	
	
	public static function em_booking_set_status($result, $booking){
		self::refresh_counts($booking); //refresh the counts in case booking was cancelled or rejected
		return $result;
	}
	
	/**
	 * Deprecated, use self::refresh_counts instead
	 */
	public static function lower_booking_count( $booking ){
		return apply_filters('em_coupons_lower_booking_count', self::refresh_counts($booking), $booking);
	}
	

	public static function refresh_counts( Booking $booking ) : bool {
		$result = true;
		$coupons = self::booking_get_coupons($booking);
		foreach( $coupons as $coupon ){ /* @var EM_Coupon $coupon */
			//$result = $coupon->recount() !== false && $result;
		}
		return apply_filters('em_coupons_refresh_counts', $result, $booking);
	}

	public static function get_query_args($args) : array
	{
		$queryArgs = [
			'meta_query'     => ['relation' => 'AND'],
			'post_type'      => CouponPost::POST_TYPE,
			'tax_query'      => [],
			'posts_per_page' => $args['limit'] ?? 100,
			'paged'		 => $args['paged'] ?? 0,
		];
		
		if(!empty($args['code'])) {
			$queryArgs['meta_query'][] = [
				'key'     => '_coupon_code',
				'value'   => $args['code'],
				'compare' => '='
			];
		}

		if(!empty($args['expired'])) {
			$queryArgs['meta_query'][] = [
				'key'     => '_coupon_expired',
				'value'   => $args['expired'],
				'compare' => '='
			];
		}

		
		return $queryArgs;
		
	}
	
	public static function find($args) : self
	{
		$query_args = self::get_query_args($args);
		$query = new \WP_Query($query_args);
		if(!$query->have_posts()) {
			return new self();
		}
		$ret = \array_map(fn($post) => Coupon::get_by_post($post), $query->posts);
		$instance = new self();
		$instance->coupons = $ret;
		return $instance;
	}

	public function jsonSerialize(): mixed
	{
		return $this->coupons;
	}

	public function getIterator() : \ArrayIterator {
		return new \ArrayIterator($this->coupons);
	}
	public function count() : int {
		return count($this->coupons);
	}
}
CouponCollection::init();