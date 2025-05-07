<?php

namespace Contexis\Events\Views;

use Contexis\Events\Intl\Price;
use Contexis\Events\Models\Coupon;
use DateInterval;
use DateTime;

class BookingView
{
	static function render($booking, $format, $target="html") : string {
	 	preg_match_all("/(#@?_?[A-Za-z0-9]+)({([^}]+)})?/", $format, $placeholders);
		$output_string = $format;
		$replaces = array();
		foreach($placeholders[1] as $key => $result) {
			$replace = '';
			$full_result = $placeholders[0][$key];
			$placeholder_atts = array($result);
			if( !empty($placeholders[3][$key]) ) $placeholder_atts[] = $placeholders[3][$key];
			switch( $result ){
				case '#_BOOKINGFORMCUSTOM':
					if(!$placeholder_atts[1]) break; 
					$replace = $booking->meta['booking'][$placeholder_atts[1]];
					break;
				case '#_BOOKINGFIELDS': 
					ob_start();
					em_locate_template('emails/bookingfields.php', true, array('booking'=>$booking));
					$replace = ob_get_clean();
					break;
				case '#_BOOKINGFIELD':
					if(!$placeholder_atts[1]) break;
					if(key_exists($placeholder_atts[1], $booking->meta['booking'])) {
						$replace = $booking->booking_meta['booking'][$placeholder_atts[1]];
						break;
					}
					if(key_exists($placeholder_atts[1], $booking->meta['registration'])) {
						$replace = $booking->booking_meta['registration'][$placeholder_atts[1]];
					}
					break;
				case '#_BOOKINGID':
					$replace = $booking->booking_id;
					break;
				case '#_BOOKINGNAME':
					$replace = $booking->get_full_name;
					break;
				case '#_BOOKINGEMAIL':
					$replace = $booking->booking_meta['registration']['user_email'];
					break;
				case '#_BOOKINGDATE':
					$replace = ( $booking->date() !== false ) ? \Contexis\Events\Intl\Date::get_date($booking->date()->getTimestamp()) :'n/a';
					break;
				case '#_BOOKINGTIME':
					$replace = ( $booking->date() !== false ) ?  \Contexis\Events\Intl\Date::get_time($booking->date()->getTimestamp()) :'n/a';
					break;
				case '#_BOOKINGCOMMENT':
					$replace = $booking->booking_comment;
				case '#_BOOKINGPRICE':
					$replace = Price::format($booking->get_price());
					break;
				case '#_BOOKINGTICKETS':
					ob_start();
					em_locate_template('emails/bookingtickets.php', true, array('booking'=>$booking));
					$replace = ob_get_clean();
					break;
				case '#_BOOKINGSUMMARY':
					ob_start();
					em_locate_template('emails/bookingsummary.php', true, array('booking'=>$booking));
					$replace = ob_get_clean();
					break;
				case '#_BOOKINGADMINURL':
				case '#_BOOKINGADMINLINK':
					$bookings_link = esc_url( add_query_arg('booking_id', $booking->booking_id, $booking->get_event()->get_bookings_url()) );
					if($result == '#_BOOKINGADMINLINK'){
						$replace = '<a href="'.$bookings_link.'">'.esc_html__('Edit Booking', 'events'). '</a>';
					}else{
						$replace = $bookings_link;
					}
					break;
				case '#_IBAN':
					$replace = get_option("em_offline_iban", true);
					break;
				case '#_BENEFICIARY':
					$replace = get_option("em_offline_beneficiary", true);
					break;
				case '#_REFERENCE':
					$replace = $booking->booking_id . "-" . $booking->get_event()->post_name . "-" . $booking->booking_meta['registration']['last_name'];
					break;
				case '#_PRICE': 
					$replace = \Contexis\Events\Intl\Price::format($booking->booking_price);
					break;
				case '#_BANK':
					$replace = get_option("em_offline_bank", true);
					break;	
				case '#_PAYMENTDEADLINE':
					$date = new DateTime();
					$interval = new DateInterval('P' . get_option("em_offline_deadline", 10) . 'D');
					$date->add($interval);
					$replace = \Contexis\Events\Intl\Date::get_date($date->getTimestamp());
					break;
				case '#_COUPON':
					$replace = $booking->get_price_adjustments_summary('discounts', 'pre');
					break;
				case '#_BOOKINGATTENDEES':
					ob_start();
					em_locate_template('emails/attendees.php', true, array('booking'=>$booking));
					$replace = ob_get_clean();
					break;
				case '#_BOOKINGCOUPON':
					$replace = '';
					if( !empty($booking->booking_meta['coupon']) ){
						$coupon = new Coupon($booking->booking_meta['coupon']);
						$replace = $coupon->coupon_code.' - '.$coupon->get_discount_text();					
					}
					break;
				case '#_BOOKINGCOUPONCODE':
					$replace = '';
					if( !empty($booking->booking_meta['coupon']) ){
						$coupon = new Coupon($booking->booking_meta['coupon']);
						$replace = $coupon->coupon_code;					
					}
					break;
				case '#_BOOKINGCOUPONDISCOUNT':
					$replace = '';
					if( !empty($booking->booking_meta['coupon']) ){
						$coupon = new Coupon($booking->booking_meta['coupon']);
						$replace = $coupon->get_discount_text();					
					}
					break;
				case '#_BOOKINGCOUPONNAME':
					$replace = '';
					if( !empty($booking->booking_meta['coupon']) ){
						$coupon = new Coupon($booking->booking_meta['coupon']);
						$replace = $coupon->coupon_name;					
					}
					break;
				case '#_BOOKINGCOUPONDESCRIPTION':
					$replace = '';
					if( !empty($booking->booking_meta['coupon']) ){
						$coupon = new Coupon($booking->booking_meta['coupon']);
						$replace = $coupon->coupon_description;					
					}
					break;
				default:
					$replace = $full_result;
					break;
			}
			$replaces[$full_result] = apply_filters('em_booking_output_placeholder', $replace, $booking, $full_result, $target, $placeholder_atts);
		}
		//sort out replacements so that during replacements shorter placeholders don't overwrite longer varieties.
		krsort($replaces);
		foreach($replaces as $full_result => $replacement){
			$output_string = str_replace($full_result, $replacement , $output_string );
		}
		//run event output too, since booking is never run from within events and will not infinitely loop
		$event = apply_filters('em_booking_output_event', $booking->get_event(), $booking); //allows us to override the booking event info if it belongs to a parent or translation
		$output_string = EventView::render($event, $output_string, $target);
		return apply_filters('em_booking_output', $output_string, $booking, $format, $target);	
	}
}

