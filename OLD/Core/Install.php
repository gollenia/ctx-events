<?php

namespace Contexis\Events\Core;

use Contexis\Events\Forms\Form;
use Contexis\Events\PostTypes\CouponPost;
use Contexis\Events\PostTypes\EventPost;
use Contexis\Events\PostTypes\FormPost;
use Contexis\Events\PostTypes\LocationPost;
use Contexis\Events\PostTypes\RecurringEventPost;
use Contexis\Events\PostTypes\SpeakerPost;
use Contexis\Events\Repositories\BookingRepository;
use Contexis\Events\Repositories\TransactionRepository;
use Mpdf\Tag\Tr;

class Install {

	public static function init() {
		$instance = new self;
	}

	public static function activate_plugin() {
		if (!class_exists('IntlDateFormatter')) {
			$plugin = plugin_basename(dirname(__DIR__, 2) . '/events.php');
			deactivate_plugins($plugin);
			wp_die(__('The Events Manager plugin requires the PHP Intl extension to be installed and enabled on your server. Please contact your hosting provider to enable it.', 'events-manager'), __('Plugin Activation Error', 'events-manager'), ['back_link' => true]);
    	}

		BookingRepository::migrate_table();
		TransactionRepository::migrate_table();
		self::register_options();
	}

	

	public static function reset_plugin() {
		global $wpdb;
	
		$wpdb->query("TRUNCATE TABLE {$wpdb->prefix}em_bookings");
		
		$posts = get_posts([
			'post_type' => [ 
				EventPost::POST_TYPE,
				LocationPost::POST_TYPE,
				SpeakerPost::POST_TYPE,
				CouponPost::POST_TYPE,
				RecurringEventPost::POST_TYPE,
				FormPost::ATTENDEE_POST_TYPE,
				FormPost::BOOKING_POST_TYPE
			],
			'numberposts' => -1,
			'post_status' => 'any'
    	]);

		foreach($posts as $post) {
			wp_delete_post($post->ID, true);
		}

		exit();
	}

	

	public static function register_options() {
	
		$respondent_email_body_localizable = __("Dear #_BOOKINGNAME, <br/>You have successfully reserved #_BOOKINGSPACES space/spaces for #_EVENTNAME.<br/>When : #_EVENTDATES @ #_EVENTTIMES<br/>Where : #_LOCATIONNAME - #_LOCATIONFULLLINE<br/>Yours faithfully,<br/>#_CONTACTNAME",'events');
		$respondent_email_pending_body_localizable = __("Dear #_BOOKINGNAME, <br/>You have requested #_BOOKINGSPACES space/spaces for #_EVENTNAME.<br/>When : #_EVENTDATES @ #_EVENTTIMES<br/>Where : #_LOCATIONNAME - #_LOCATIONFULLLINE<br/>Your booking is currently pending approval by our administrators. Once approved you will receive an automatic confirmation.<br/>Yours faithfully,<br/>#_CONTACTNAME",'events');
		$respondent_email_rejected_body_localizable = __("Dear #_BOOKINGNAME, <br/>Your requested booking for #_BOOKINGSPACES spaces at #_EVENTNAME on #_EVENTDATES has been rejected.<br/>Yours faithfully,<br/>#_CONTACTNAME",'events');
		$respondent_email_cancelled_body_localizable = __("Dear #_BOOKINGNAME, <br/>Your requested booking for #_BOOKINGSPACES spaces at #_EVENTNAME on #_EVENTDATES has been cancelled.<br/>Yours faithfully,<br/>#_CONTACTNAME",'events');
		
		$event_submitted_email_body = __("A new event has been submitted by #_CONTACTNAME.<br/>Name : #_EVENTNAME <br/>Date : #_EVENTDATES <br/>Time : #_EVENTTIMES <br/>Please visit #_EDITEVENTURL to review this event for approval.",'events');
		$event_submitted_email_body = str_replace('#_EDITEVENTURL', admin_url().'post.php?action=edit&post=#_EVENTPOSTID', $event_submitted_email_body);
		$event_published_email_body = __("A new event has been published by #_CONTACTNAME.<br/>Name : #_EVENTNAME <br/>Date : #_EVENTDATES <br/>Time : #_EVENTTIMES <br/>Edit this event - #_EDITEVENTURL <br/> View this event - #_EVENTURL",'events');
		$event_published_email_body = str_replace('#_EDITEVENTURL', admin_url().'post.php?action=edit&post=#_EVENTPOSTID', $event_published_email_body);
		$event_resubmitted_email_body = __("A previously published event has been modified by #_CONTACTNAME, and this event is now unpublished and pending your approval.<br/>Name : #_EVENTNAME <br/>Date : #_EVENTDATES <br/>Time : #_EVENTTIMES <br/>Please visit #_EDITEVENTURL to review this event for approval.",'events');
		$event_resubmitted_email_body = str_replace('#_EDITEVENTURL', admin_url().'post.php?action=edit&post=#_EVENTPOSTID', $event_resubmitted_email_body);

		//event admin emails - new format to the above, standard format plus one unique line per booking status at the top of the body and subject line
		$contact_person_email_body_template = '#_EVENTNAME - #_EVENTDATES @ #_EVENTTIMES'.'<br/>'
				.__('Now there are #_BOOKEDSPACES spaces reserved, #_AVAILABLESPACES are still available.','events').'<br/>'.
				strtoupper(__('Booking Details','events')).'<br/>'.
				__('Name','events').' : #_BOOKINGNAME'.'<br/>'.
				__('Email','events').' : #_BOOKINGEMAIL'.'<br/>'.
				'#_BOOKINGSUMMARY'.'<br/>'.
				'<br/>Powered by Events Manager - http://wp-events-plugin.com';
		$contact_person_emails['confirmed'] = sprintf(__('The following booking is %s :','events'),strtolower(__('Confirmed','events'))).'<br/>'.$contact_person_email_body_template;
		$contact_person_emails['pending'] = sprintf(__('The following booking is %s :','events'),strtolower(__('Pending','events'))).'<br/>'.$contact_person_email_body_template;
		$contact_person_emails['cancelled'] = sprintf(__('The following booking is %s :','events'),strtolower(__('Cancelled','events'))).'<br/>'.$contact_person_email_body_template;
		$contact_person_emails['rejected'] = sprintf(__('The following booking is %s :','events'),strtolower(__('Rejected','events'))).'<br/>'.$contact_person_email_body_template;
		//registration email content
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		$respondent_email_body_localizable = __("Dear #_BOOKINGNAME, <br />This is a reminder about your #_BOOKINGSPACES space/spaces reserved for #_EVENTNAME.<br />When : #_EVENTDATES @ #_EVENTTIMES<br />Where : #_LOCATIONNAME - #_LOCATIONFULLLINE<br />We look forward to seeing you there!<br />Yours faithfully,<br />#_CONTACTNAME",'events');
		//all the options
		$dbem_options = array(
			'dbem_events_default_orderby' => 'event_start_date,event_start_time,event_name',
			'dbem_events_default_order' => 'ASC',
			//Email Config
			
			'dbem_smtp_html' => 1,
			'dbem_smtp_html_br' => 1,

			//General Settings
			'dbem_locations_enabled' => 1,
			'dbem_recurrence_enabled'=> 1,
			'dbem_rsvp_enabled'=> 1,
			
			//Bookings
			'dbem_bookings_approval' => 1, //approval is on by default
			'dbem_bookings_approval_reserved' => 0, //overbooking before approval?
			'dbem_bookings_approval_overbooking' => 0, //overbooking possible when approving?
			
			'dbem_bookings_currency' => 'USD',
			
				//Form Options
			'dbem_booking_feedback_pending' =>__('Booking successful, pending confirmation (you will also receive an email once confirmed).', 'events'),
			'dbem_booking_feedback' => __('Booking successful.', 'events'),
			'dbem_booking_feedback_full' => __('Booking cannot be made, not enough spaces available!', 'events'),
			//Emails
			'dbem_bookings_notify_admin' => 0,
			'dbem_bookings_contact_email' => 1,
			'dbem_bookings_contact_email_pending_subject' => __("Booking Pending",'events'),
			'dbem_bookings_contact_email_pending_body' => str_replace("<br/>", "\n\r", $contact_person_emails['pending']),
			'dbem_bookings_contact_email_confirmed_subject' => __('Booking Confirmed','events'),
			'dbem_bookings_contact_email_confirmed_body' => str_replace("<br/>", "\n\r", $contact_person_emails['confirmed']),
			'dbem_bookings_contact_email_rejected_subject' => __("Booking Rejected",'events'),
			'dbem_bookings_contact_email_rejected_body' => str_replace("<br/>", "\n\r", $contact_person_emails['rejected']),
			'dbem_bookings_contact_email_cancelled_subject' => __("Booking Cancelled",'events'),
			'dbem_bookings_contact_email_cancelled_body' => str_replace("<br/>", "\n\r", $contact_person_emails['cancelled']),
			'dbem_bookings_email_pending_subject' => __("Booking Pending",'events'),
			'dbem_bookings_email_pending_body' => str_replace("<br/>", "\n\r", $respondent_email_pending_body_localizable),
			'dbem_bookings_email_rejected_subject' => __("Booking Rejected",'events'),
			'dbem_bookings_email_rejected_body' => str_replace("<br/>", "\n\r", $respondent_email_rejected_body_localizable),
			'dbem_bookings_email_confirmed_subject' => __('Booking Confirmed','events'),
			'dbem_bookings_email_confirmed_body' => str_replace("<br/>", "\n\r", $respondent_email_body_localizable),
			'dbem_bookings_email_cancelled_subject' => __('Booking Cancelled','events'),
			'dbem_bookings_email_cancelled_body' => str_replace("<br/>", "\n\r", $respondent_email_cancelled_body_localizable),
			'dbem_bookings_tickets_ordering' => 1,
			'dbem_bookings_tickets_orderby' => 'ticket_price DESC, ticket_name ASC',
			'dbem_cp_events_slug' => 'events',
			'dbem_cp_events_search_results' => 0,
			'dbem_custom_emails' => 0,
			'dbem_custom_emails_events' => 1,
			'dbem_custom_emails_events_admins' => 1,
			'dbem_custom_emails_gateways' => 1,
			'dbem_custom_emails_gateways_admins' => 1,

			'dbem_bookings_ical_attachments' => 1,
			
			'dbem_cron_emails' => 0,
			'dbem_cron_emails_limit' => get_option('emp_cron_emails_limit', 100),
			'dbem_emp_emails_reminder_subject' => __('Reminder','events').' - #_EVENTNAME',
			'dbem_emp_emails_reminder_body' => str_replace("<br />", "\n\r", $respondent_email_body_localizable),
			'dbem_emp_emails_reminder_time' => '12:00 AM',
			'dbem_emp_emails_reminder_days' => 1,
			'dbem_emp_emails_reminder_ical' => 1,
			//offline
			'em_offline_booking_feedback' => __('Booking successful.', 'events')
		);
		
		//add new options
		foreach($dbem_options as $key => $value){
			add_option($key, $value);
		}

		$booking_form_data = array( 'name'=> __('Default','events'), 'form'=> array (
			'name' => array ( 'label' => __('Name','events'), 'type' => 'name', 'fieldid'=>'user_name', 'required'=>1 ),
			'user_email' => array ( 'label' => __('Email','events'), 'type' => 'user_email', 'fieldid'=>'user_email', 'required'=>1 ),
			'dbem_address' => array ( 'label' => __('Address','events'), 'type' => 'dbem_address', 'fieldid'=>'dbem_address', 'required'=>1 ),
			'dbem_city' => array ( 'label' => __('City/Town','events'), 'type' => 'dbem_city', 'fieldid'=>'dbem_city', 'required'=>1 ),
			'dbem_state' => array ( 'label' => __('State/County','events'), 'type' => 'dbem_state', 'fieldid'=>'dbem_state', 'required'=>1 ),
			'dbem_zip' => array ( 'label' => __('Zip/Post Code','events'), 'type' => 'dbem_zip', 'fieldid'=>'dbem_zip', 'required'=>1 ),
			'dbem_country' => array ( 'label' => __('Country','events'), 'type' => 'dbem_country', 'fieldid'=>'dbem_country', 'required'=>1 ),
			'dbem_phone' => array ( 'label' => __('Phone','events'), 'type' => 'dbem_phone', 'fieldid'=>'dbem_phone' ),
			'booking_comment' => array ( 'label' => __('Comment','events'), 'type' => 'textarea', 'fieldid'=>'booking_comment' ),
		));
	

			
		
	}
}