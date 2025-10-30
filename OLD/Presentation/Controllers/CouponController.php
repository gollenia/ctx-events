<?php

namespace Contexis\Events\Controllers;

use Contexis\Events\Models\Booking;
use Contexis\Events\Models\Coupon;
use Contexis\Events\Models\Event;

class CouponController
{
	public static function init() {
		$instance = new self();
		add_action( 'rest_api_init', [$instance, 'register_rest_routes'], 10 );
		add_action('wp_ajax_em_export_coupons_xls', array($instance, 'export_coupons_xls') );
		add_action('wp_ajax_em_export_coupon_xls', array($instance, 'export_coupon_xls') );
	}

	public function register_rest_routes() : void {
		register_rest_route('events/v2', '/coupon/check', ['method' => 'GET', 'callback' => [$this, 'coupon_validate'], 'permission_callback' => fn() => true ]);
		register_rest_route( 'events/v2', '/coupons/export', array(
			'methods' => 'GET',
			'callback' => [$this, 'coupon_export'],
			'permission_callback' => function() {
				return true;
			}
		) );
		register_rest_route( 'events/v2', '/coupon/export/(?P<id>\d+)', array(
			'methods' => 'GET',
			'callback' => [$this, 'coupon_export_single'],
			'permission_callback' => function() {
				return true;
			}
		) );
	}

	public function coupon_validate(\WP_REST_Request $request) : array {
		
		$result = [
			'success'=>false, 
			'message'=> __('Coupon Not Found', 'events'),
			'discount' => 0,
			'percent' => false,
			'code' => ''
		];

		$event_id = $request->get_param( 'event_id' );
		$code = $request->get_param( 'code' );

		if(!$event_id) return array_merge($result, ["message" => __('No event given','events')]);
		if(!$code) return array_merge($result, ["message" => __('No coupon code given','events')]);
		$event = Event::get_by_id($event_id);
		$coupon = Coupon::get_by_code($request->get_param('code'));
		
		if (empty($event->event_id) || !is_object($coupon)) return array_merge($result, ["message" => __('Coupon Invalid','events')]);

		if(!$coupon->validate($event)) return $result;
	
		$result['success'] = true;
		$result['discount'] = intval($coupon->discount);
		$result['percent'] = $coupon->type != "#";
		$result['message'] = $coupon->description;
		$result['code'] = $coupon->code;
		
		return  $result;
		
	}

	public static function export_coupons_xls() {
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

			$discount = ($coupon->type == "#" ? '€' : '') . $coupon->discount . ($coupon->type == "%" ? '%' : '');
			$array[] = [
				$coupon->name,
				$coupon->code,
				$coupon->description,
				$discount,
				$coupon->get_count(),
				$coupon->max
			];
		}

		
		$xlsx = \Shuchkin\SimpleXLSXGen::fromArray( $array );
		$xlsx->downloadAs('coupons.xlsx');

	}

	public static function export_coupon_xls($request) {	
		global $wpdb;	
		$coupon = new Coupon($request->get_param('id'));
		
		$limit = ( !empty($_GET['limit']) ) ? $_GET['limit'] : 20;//Default limit
		$page = ( !empty($_GET['pno']) ) ? $_GET['pno']:1;
		$offset = ( $page > 1 ) ? ($page-1)*$limit : 0;
		/* @todo change how coupon-booking relations are stored */
		$coupon_search = str_replace('a:1:{', '', serialize(array('coupon_code'=>$coupon->code)));
		$coupon_search = substr($coupon_search, 0, strlen($coupon_search)-1 );
		$sql = $wpdb->prepare('SELECT id FROM '.EM_BOOKINGS_TABLE." WHERE coupon_code '%{$coupon_search}%' LIMIT {$limit} OFFSET {$offset}");
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

			if( empty($booking->coupon_id) ) continue;
			
			$coupon = Coupon::get_by_id($booking->coupon_id);
			if($coupon->code == $coupon->code && $coupon->id == $coupon->id){
				$base_price = $booking->get_price();
				$bookings_count++;
				$result[] = [
					$booking->id,
					$booking->get_event()->event_name,
					\Contexis\Events\Intl\Date::get_date($booking->date()->getTimestamp()),
					$booking->get_price(),
					$booking->get_full_name(),
					$booking->user_email,
					$booking->get_booked_spaces(),
					$coupon->name,
					\Contexis\Events\Intl\Price::format( $booking->get_price()),
					\Contexis\Events\Intl\Price::format($coupon->get_discount($base_price)),
					\Contexis\Events\Intl\Price::format($booking->get_price()),
				];
			}
			
		}

		$xlsx = \Shuchkin\SimpleXLSXGen::fromArray( $result );
		$xlsx->downloadAs('coupons.xlsx');
	}
}