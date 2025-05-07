<?php

use Contexis\Events\Collections\EventCollection;
use Contexis\Events\Models\Location;
use Contexis\Events\PostTypes\EventPost;
use Contexis\Events\PostTypes\LocationPost;
use Contexis\Events\Utilities\Plugin;

function em_uninstall() {
	global $wpdb;

	$post_ids = $wpdb->get_col('SELECT ID FROM '.$wpdb->posts." WHERE post_type IN ('".EventPost::POST_TYPE."','".LocationPost::POST_TYPE."','event-recurring')");

	foreach($post_ids as $post_id){
		wp_delete_post($post_id);
	}

	$cat_terms = get_terms(EventPost::CATEGORIES, array('hide_empty'=>false));
	foreach($cat_terms as $cat_term){
		wp_delete_term($cat_term->term_id, EventPost::CATEGORIES);
	}
	$tag_terms = get_terms(EventPost::TAGS, array('hide_empty'=>false));
	foreach($tag_terms as $tag_term){
		wp_delete_term($tag_term->term_id, EventPost::TAGS);
	}
	//delete EM tables
	$wpdb->query('DROP TABLE '.EM_BOOKINGS_TABLE);
	$wpdb->query('DROP TABLE '.EM_META_TABLE);
	
	//delete options
	$wpdb->query('DELETE FROM '.$wpdb->options.' WHERE option_name LIKE \'em_%\' OR option_name LIKE \'dbem_%\'');
	//deactivate and go!
	deactivate_plugins(array('events/events.php','events-pro/events-pro.php'), true);
	wp_safe_redirect(admin_url('plugins.php?deactivate=true'));
	exit();
}




function em_install() {

    $installed_version = Plugin::get_installed_version();
    $plugin_version = Plugin::get_plugin_version();

    if (version_compare($installed_version, $plugin_version, '>=')) {
        return;
    }
   	
	if( $installed_version === '0.0.0' ){
		if (get_option('dbem_upgrade_throttle', 0) <= time()) {
			update_option('dbem_upgrade_throttle', time() + 60);
			em_create_events_meta_table();
			em_create_bookings_table();
			em_create_reminders_table();
			em_add_options();
		}
	}
	delete_option('dbem_upgrade_throttle');

	if( version_compare($installed_version, $plugin_version, '<') ){
		em_upgrade_current_installation();
		update_option('dbem_version', $plugin_version);
	}
	

	global $wp_rewrite;
	$wp_rewrite->flush_rules();
	
	
	update_option('dbem_flush_needed',1);
	add_action ( 'admin_notices', function() use ($plugin_version) { 
		echo '<div class="updated"><p>'.__('Events has been updated to version ',  'events'). $plugin_version. '</p></div>';
	});
}

/**
 * Magic function that takes a table name and cleans all non-unique keys not present in the $clean_keys array. if no array is supplied, all but the primary key is removed.
 * @param string $table_name
 * @param array $clean_keys
 */
function em_sort_out_table_nu_keys($table_name, $clean_keys = array()){
	global $wpdb;
	//sort out the keys
	$new_keys = $clean_keys;
	$table_key_changes = array();
	$table_keys = $wpdb->get_results("SHOW KEYS FROM $table_name WHERE Key_name != 'PRIMARY'", ARRAY_A);
	foreach($table_keys as $table_key_row){
		if( !in_array($table_key_row['Key_name'], $clean_keys) ){
			$table_key_changes[] = "ALTER TABLE $table_name DROP INDEX ".$table_key_row['Key_name'];
		}elseif( in_array($table_key_row['Key_name'], $clean_keys) ){
			foreach($clean_keys as $key => $clean_key){
				if($table_key_row['Key_name'] == $clean_key){
					unset($new_keys[$key]);
				}
			}
		}
	}
	//delete duplicates
	foreach($table_key_changes as $sql){
		$wpdb->query($sql);
	}
	//add new keys
	foreach($new_keys as $key){
		if( preg_match('/\(/', $key) ){
			$wpdb->query("ALTER TABLE $table_name ADD INDEX $key");
		}else{
			$wpdb->query("ALTER TABLE $table_name ADD INDEX ($key)");
		}
	}
}

function em_create_events_meta_table(){
	global  $wpdb, $user_level;
	$table_name = $wpdb->prefix.'em_meta';

	// Creating the events table
	$sql = "CREATE TABLE ".$table_name." (
		meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		object_id bigint(20) unsigned NOT NULL,
		meta_key varchar(255) DEFAULT NULL,
		meta_value longtext,
		meta_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (meta_id)
		) DEFAULT CHARSET=utf8 ";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	dbDelta($sql);
	em_sort_out_table_nu_keys($table_name, array('object_id','meta_key'));
}

function em_create_bookings_table() {

	global  $wpdb, $user_level;
	$table_name = $wpdb->prefix.'em_bookings';

	$sql = "CREATE TABLE ".$table_name." (
		booking_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		event_id bigint(20) unsigned NULL,
		booking_spaces smallint(5) NOT NULL,
		booking_comment text DEFAULT NULL,
		booking_mail TEXT NULL DEFAULT NULL,
		booking_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		booking_status bool NOT NULL DEFAULT 1,
 		booking_price decimal(14,4) unsigned NOT NULL DEFAULT 0,
 		booking_donation decimal(10,2) unsigned NOT NULL DEFAULT 0,
		booking_meta LONGTEXT NULL,
		PRIMARY KEY  (booking_id)
		) DEFAULT CHARSET=utf8 ;";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	em_sort_out_table_nu_keys($table_name, array('event_id','booking_status'));
}


//Add the categories table




function em_create_reminders_table(){
	global  $wpdb;
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php'); 
    $table_name = $wpdb->prefix.'em_email_queue';
	$sql = "CREATE TABLE ".$table_name." (
		  queue_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		  event_id bigint(20) unsigned DEFAULT NULL,
		  booking_id bigint(20) unsigned DEFAULT NULL,
		  email text NOT NULL,
		  subject text NOT NULL,
		  body text NOT NULL,
		  attachment text NOT NULL,
		  PRIMARY KEY  (queue_id)
		) DEFAULT CHARSET=utf8 ;";
	dbDelta($sql);
	em_sort_out_table_nu_keys($table_name,array('event_id','booking_id'));
}

function em_add_options() {
	global $wp_locale, $wpdb;
	$email_footer = '<br/><br/>-------------------------------<br/>Powered by Events Manager - http://wp-events-plugin.com';
	$respondent_email_body_localizable = __("Dear #_BOOKINGNAME, <br/>You have successfully reserved #_BOOKINGSPACES space/spaces for #_EVENTNAME.<br/>When : #_EVENTDATES @ #_EVENTTIMES<br/>Where : #_LOCATIONNAME - #_LOCATIONFULLLINE<br/>Yours faithfully,<br/>#_CONTACTNAME",'events').$email_footer;
	$respondent_email_pending_body_localizable = __("Dear #_BOOKINGNAME, <br/>You have requested #_BOOKINGSPACES space/spaces for #_EVENTNAME.<br/>When : #_EVENTDATES @ #_EVENTTIMES<br/>Where : #_LOCATIONNAME - #_LOCATIONFULLLINE<br/>Your booking is currently pending approval by our administrators. Once approved you will receive an automatic confirmation.<br/>Yours faithfully,<br/>#_CONTACTNAME",'events').$email_footer;
	$respondent_email_rejected_body_localizable = __("Dear #_BOOKINGNAME, <br/>Your requested booking for #_BOOKINGSPACES spaces at #_EVENTNAME on #_EVENTDATES has been rejected.<br/>Yours faithfully,<br/>#_CONTACTNAME",'events').$email_footer;
	$respondent_email_cancelled_body_localizable = __("Dear #_BOOKINGNAME, <br/>Your requested booking for #_BOOKINGSPACES spaces at #_EVENTNAME on #_EVENTDATES has been cancelled.<br/>Yours faithfully,<br/>#_CONTACTNAME",'events').$email_footer;
	
	$event_submitted_email_body = __("A new event has been submitted by #_CONTACTNAME.<br/>Name : #_EVENTNAME <br/>Date : #_EVENTDATES <br/>Time : #_EVENTTIMES <br/>Please visit #_EDITEVENTURL to review this event for approval.",'events').$email_footer;
	$event_submitted_email_body = str_replace('#_EDITEVENTURL', admin_url().'post.php?action=edit&post=#_EVENTPOSTID', $event_submitted_email_body);
	$event_published_email_body = __("A new event has been published by #_CONTACTNAME.<br/>Name : #_EVENTNAME <br/>Date : #_EVENTDATES <br/>Time : #_EVENTTIMES <br/>Edit this event - #_EDITEVENTURL <br/> View this event - #_EVENTURL",'events').$email_footer;
	$event_published_email_body = str_replace('#_EDITEVENTURL', admin_url().'post.php?action=edit&post=#_EVENTPOSTID', $event_published_email_body);
	$event_resubmitted_email_body = __("A previously published event has been modified by #_CONTACTNAME, and this event is now unpublished and pending your approval.<br/>Name : #_EVENTNAME <br/>Date : #_EVENTDATES <br/>Time : #_EVENTTIMES <br/>Please visit #_EDITEVENTURL to review this event for approval.",'events').$email_footer;
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
	$respondent_email_body_localizable = __("Dear #_BOOKINGNAME, <br />This is a reminder about your #_BOOKINGSPACES space/spaces reserved for #_EVENTNAME.<br />When : #_EVENTDATES @ #_EVENTTIMES<br />Where : #_LOCATIONNAME - #_LOCATIONFULLLINE<br />We look forward to seeing you there!<br />Yours faithfully,<br />#_CONTACTNAME",'events').$email_footer;
	//all the options
	$dbem_options = array(
		'dbem_default_location'=>0,
		'dbem_events_default_orderby' => 'event_start_date,event_start_time,event_name',
		'dbem_events_default_order' => 'ASC',
		//Location Formatting
		'dbem_location_default_country' => '',
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
		//Registration Email
		//Ticket Specific Options
		'dbem_bookings_tickets_ordering' => 1,
		'dbem_bookings_tickets_orderby' => 'ticket_price DESC, ticket_name ASC',

		//My Bookings Page
		'dbem_cp_events_slug' => 'events',
		//event cp options
		'dbem_cp_events_search_results' => 0,
	    //feedback reminder
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
		'em_offline_booking_feedback' => __('Booking successful.', 'events'),
		'emp_gateway_customer_fields' => ['address' => 'dbem_address','address_2' => 'dbem_address_2','city' => 'dbem_city','state' => 'dbem_state','zip' => 'dbem_zip','country' => 'dbem_country','phone' => 'dbem_phone','company' => 'dbem_company'],
		
		
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
  
	  //Booking form stuff only run on install
	$wpdb->insert(EM_META_TABLE, array('meta_value'=>serialize($booking_form_data), 'meta_key'=>'booking-form','object_id'=>0));
	add_option('em_booking_form_fields', $wpdb->insert_id);
		
	
}

function em_upgrade_current_installation(){
	global $wpdb;
	add_action('admin_notices', function(){
		echo '<div class="updated"><p>'.__('Updating').'</p></div>';
	});

	$installed_version = Plugin::get_installed_version();

	if( version_compare($installed_version, '6.7.0') < 0 ){
		

	}

	if( version_compare($installed_version, '6.8.7') < 0 ){
		$wpdb->query('ALTER TABLE '.EM_BOOKINGS_TABLE.' ADD COLUMN booking_donation decimal(10,2) NULL DEFAULT NULL');
		$wpdb->query('ALTER TABLE '.EM_BOOKINGS_TABLE.' DROP COLUMN booking_tax');
		$wpdb->query('ALTER TABLE '.EM_BOOKINGS_TABLE.' DROP COLUMN booking_tax_rate');
		$wpdb->query('ALTER TABLE '.EM_BOOKINGS_TABLE.' ALTER COLUMN booking_donation SET TYPE decimal(10,2)');
		$wpdb->query('ALTER TABLE '.EM_BOOKINGS_TABLE.' ADD COLUMN booking_mail TEXT NULL');
		$wpdb->query('ALTER TABLE '.EM_BOOKINGS_TABLE.' DROP COLUMN booking_tax');
		$wpdb->query('ALTER TABLE '.EM_BOOKINGS_TABLE.' DROP COLUMN person_id');
		delete_option('dbem_bookings_tax');
		delete_option('dbem_bookings_tax_auto_add');

	}

	if( version_compare($installed_version, '6.9.0') < 0 ){

		$batch_size = 100;
		$offset = 0;
		
		while($events = EventCollection::find(['limit' => $batch_size, 'paged' => $offset])) {
			if($offset == 100) break;

			foreach($events as $event) {
				if($event->location_id == 0) continue;
				$location = Location::find_by_location_id($event->location_id);
				if(!$location) {
				
					continue;
				}
				if($location->post_id == 0) {
				
					continue;
				}
				$event->location_id = $location->post_id;
				update_post_meta($event->post_id, '_location_id', $location->post_id);
				$event->save();
			
			}
			$offset += $batch_size;
		}
		global $wpdb;
		
	}

	if( version_compare($installed_version, '6.9.1') < 0 ){
		
	}
}



?>