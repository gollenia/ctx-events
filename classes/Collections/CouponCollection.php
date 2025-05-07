<?php

namespace Contexis\Events\Collections;

use Contexis\Events\Collections\BookingCollection;
use Contexis\Events\Models\Booking;
use Contexis\Events\Models\Coupon;
use Contexis\Events\Models\Event;
use Contexis\Events\PostTypes\CouponPost;
use WP_REST_Request;

class CouponCollection implements \IteratorAggregate, \Countable {

	private array $coupons = [];
     
	public static function init(){
	    
		//add field to booking form and ajax
		$instance = new self;
		
		add_action('rest_api_init', [$instance, 'register_rest_routes']);
		//hook into booking submission to add discount and coupon info
		
		add_filter('em_booking_validate', array($instance, 'em_booking_validate'), 10, 2);
		
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
					'name' => $coupon->coupon_code . ' - '. $coupon->get_discount_text(),
					'type'=> $coupon->coupon_type,
					'amount'=> $coupon->coupon_discount,
					'desc' => $coupon->coupon_name,
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
	

	
	/**
	 * Gets all coupons available to an event
	 * @param Event $event
	 * @return array
	 */
	public static function event_get_coupons(int $event_id) : self
	{
	    $ids = get_post_meta($event_id, '_event_coupons', true) ?? [];
		$ids = array_filter(array_map('intval', $ids));
		$coupons = new self();
		foreach($ids as $id) {
			$coupon = new Coupon($id);
			$coupons->add($coupon);
		}
		return $coupons;
	}

	public static function get_sitewide_coupons() : self
	{
		$args = [
			'meta_query' => [
				[
					'key' => '_coupon_event',
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
	public static function event_get_coupon_ids($event){
	    if( empty($event->coupon_ids) ){
	    	if( !empty($event->event_id) ){
	    		//$event->coupon_ids = self::get(array('event'=>$event->event_id, 'ids'=>true));
	    	}else{
	    		$event->coupon_ids = array();
	    	}
	    }
	    return $event->coupon_ids;
	}
	
	/**
	 * @param Event $event
	 * @return boolean
	 */
	public static function event_has_coupons($event){
	    if( empty($event->event_id) ) return false;
	    $coupons = self::event_get_coupon_ids($event);
	    return !empty($coupons);
	}
	
	/* Booking Helpers */
	public static function booking_has_coupons($booking){
	    return !empty($booking->booking_meta['coupon']) || !empty($booking->booking_meta['coupons']);
	}
	

	public static function booking_get_coupons(Booking $booking){
	    $coupons = array();
	    if( empty($booking->booking_meta['coupon']) ) return $coupons;
	
	    $coupon = new Coupon($booking->booking_meta['coupon']);
	    $coupons[$coupon->coupon_id] = $coupon;
	    
	    return $coupons;
	}
	
	/* Multiple Booking Functions */
	
	public static function cart_coupon_apply( $coupon_code ){
		if(empty($_REQUEST['coupon_code'])) return false;
		if(!empty($_REQUEST['coupon_code'])){
			$coupon = new Coupon($_REQUEST['coupon_code'], 'code');
			if( !empty($coupon->coupon_id) ){
				if( $coupon->is_valid() ){
					return true;
				}
			}
		}
		return false;
	}


		
	public static function em_booking_validate(bool $result, Booking $booking) : bool {
		return $result;
	}

	public function register_rest_routes() : void {
		register_rest_route('events/v2', '/check_coupon', ['method' => 'GET', 'callback' => [$this, 'coupon_validate'], 'permission_callback' => fn() => true ]);
		register_rest_route( 'events/v2', '/coupons/export', array(
			'methods' => 'GET',
			'callback' => [$this, 'coupon_export'],
			'permission_callback' => function() {
				return true;
			}
		) );
		register_rest_route( 'events/v2', '/coupons/export_single/(?P<id>\d+)', array(
			'methods' => 'GET',
			'callback' => [$this, 'coupon_export_single'],
			'permission_callback' => function() {
				return true;
			}
		) );
	}

	public static function coupon_validate(WP_REST_Request $request) : array {
		
		$result = [
			'success'=>false, 
			'message'=> __('Coupon Not Found', 'events'),
			'discount' => 0,
			'percent' => false,
			'code' => ''
		];

		$event_id = $request->get_param( 'event_id' );

		if(!$event_id) return array_merge($result, ["message" => __('No event given','events')]);
	
		$event = Event::get_by_id($event_id);
		$coupon = Coupon::get_by_code($request->get_param('code'), $event);

		if (empty($event->event_id) || !is_object($coupon)) return array_merge($result, ["message" => __('Coupon Invalid','events')]);

		if(!$coupon->is_valid()) return $result;
	
		$result['success'] = true;
		$result['discount'] = intval($coupon->coupon_discount);
		$result['percent'] = $coupon->coupon_type != "#";
		$result['message'] = $coupon->coupon_description;
		$result['code'] = $coupon->coupon_code;
		
		return  $result;
		
	}

	public static function coupon_export() {
		$coupons = [];

		$array = [
			[
				'<b>' . __("Name", "events") .'</b>',
				'<b>' . __("Code", "events") .'</b>',
				'<b>' . __("Description", "events") .'</b>',
				'<b>' . __("Discount", "events") .'</b>',
				'<b>' . __("Uses", "events") .'</b>',
				'<b>' . __("Count", "events") .'</b>'
			]
		];

		foreach($coupons as $coupon) {

			$discount = ($coupon->coupon_type == "#" ? '€' : '') . $coupon->coupon_discount . ($coupon->coupon_type == "%" ? '%' : '');
			$array[] = [
				$coupon->coupon_name,
				$coupon->coupon_code,
				$coupon->coupon_description,
				$discount,
				$coupon->get_count(),
				$coupon->coupon_max
			];
		}

		
		$xlsx = \Shuchkin\SimpleXLSXGen::fromArray( $array );
		$xlsx->downloadAs('coupons.xlsx');

	}

	public static function coupon_export_single($request) {	
		global $wpdb;	
		$coupon = new Coupon($request->get_param('id'));
		
		$limit = ( !empty($_GET['limit']) ) ? $_GET['limit'] : 20;//Default limit
		$page = ( !empty($_GET['pno']) ) ? $_GET['pno']:1;
		$offset = ( $page > 1 ) ? ($page-1)*$limit : 0;
		/* @todo change how coupon-booking relations are stored */
		$coupon_search = str_replace('a:1:{', '', serialize(array('coupon_code'=>$coupon->coupon_code)));
		$coupon_search = substr($coupon_search, 0, strlen($coupon_search)-1 );
		$sql = $wpdb->prepare('SELECT booking_id FROM '.EM_BOOKINGS_TABLE." WHERE booking_meta LIKE '%{$coupon_search}%' LIMIT {$limit} OFFSET {$offset}");
		$bookings = $wpdb->get_col($sql);
		$bookings_count = 0;
		$result = array(
			[
				"<b>" . __("ID", "events") . "</b>",
				"<b>" . __("Event", "events") . "</b>",
				"<b>" . __("Booking Date", "events") . "</b>",
				"<b>" . __("Price", "events") . "</b>",
				"<b>" . __("Booker", "events") . "</b>",
				"<b>" . __("Email", "events") . "</b>",
				"<b>" . __('Spaces', 'events') . "</b>",
				"<b>" . __("Coupon Name", "events") . "</b>",
				"<b>" . __('Original Total Price','events', "events") . "</b>",
				"<b>" . __("Discount", "events") . "</b>",
				"<b>" . __("Final Price", "events") . "</b>"
				
			]
		);
		foreach($bookings as $booking_id){ 
			$booking = Booking::get_by_id(absint($booking_id));

			if( empty($booking->booking_meta['coupon']) ) continue;
			
			$coupon = Coupon::get_by_id($booking->booking_meta['coupon']);
			if($coupon->coupon_code == $coupon->coupon_code && $coupon->coupon_id == $coupon->coupon_id){
				$base_price = $booking->get_price();
				$bookings_count++;
				$result[] = [
					$booking->booking_id,
					$booking->get_event()->event_name,
					\Contexis\Events\Intl\Date::get_date($booking->date()->getTimestamp()),
					$booking->get_price(),
					$booking->get_full_name,
					$booking->booking_mail,
					$booking->get_booked_spaces(),
					$coupon->coupon_name,
					\Contexis\Events\Intl\Price::format( $booking->get_price()),
					\Contexis\Events\Intl\Price::format($coupon->get_discount($base_price)),
					\Contexis\Events\Intl\Price::format($booking->get_price()),
				];
			}
			
		}

		$xlsx = \Shuchkin\SimpleXLSXGen::fromArray( $result );
		$xlsx->downloadAs('coupons.xlsx');
	}

	
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
			foreach( $booking_collection->bookings as $booking ){
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
	


	public static function get_options(Event $event) : array {
		$coupons = [];
		foreach(self::event_get_coupons($event->event_id) as $coupon) {
			$coupons[] = (array) $coupon->get_option_field();
		}
		return $coupons;
	}

	public function getIterator() : \ArrayIterator {
		return new \ArrayIterator($this->coupons);
	}
	public function count() : int {
		return count($this->coupons);
	}
}
CouponCollection::init();