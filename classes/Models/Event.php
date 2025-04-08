<?php

namespace Contexis\Events\Models;

use \Contexis\Events\PostTypes\EventPost;
use Contexis\Events\Intl\Price;
use Contexis\Events\Models\Location;
use DateTime;
use Contexis\Events\Collections\BookingCollection;
use Contexis\Events\Views\EventView;
use RecurringEventPost;
use WP_Post;
use WP_User;

class Event extends \EM_Object { 

	public int $event_id = 0;
	public string $event_slug;
	public int $event_owner = 0;
	public string $event_name;
	public int $location_id = 0;
	
	public array $coupon_ids = [];
	public array $coupons = [];
	public int $coupons_count = 0;
	
	protected ?DateTime $event_start = null;
	protected ?DateTime $event_end = null;
	public bool $event_all_day = false;
	protected string $event_timezone;
	
	public bool $event_rsvp = false;
	protected ?DateTime $event_rsvp_start = null;
	protected ?DateTime $event_rsvp_end = null;
	public bool $event_rsvp_donation = false;
	public int $event_rsvp_spaces = 0;
	public string $event_date_modified;
	public string $event_date_created;
	var $event_spaces;
	var $recurrence_id;
	
	/* new attributes */
	public string $event_audience = "";
	public int $speaker_id = 0;

	private ?Location $location = null;
	private ?BookingCollection $bookings = null;
	var $contact;
	var $categories;
	var $tags;
	public array $errors = array();
	public string $feedback_message;

	public int $post_id = 0;
	private ?DateTime $post_date = null;
	var $post_title;
	var $post_excerpt = '';
	public string $post_status;
	var $post_name;
	var $post_content;
	
	var $post_type;
	var $filter;

	
	/**
	 * When cloning this event, we get rid of the bookings and location objects, since they can be retrieved again from the cache instead. 
	 */
	public function __clone(){
		$this->bookings = null;
		$this->location = null;
	}

	public static function find_by_id(int $event_id) : ?Event
	{
		return self::find_by_post_id($event_id);
	}

	public static function find_by_post_id(int $post_id = 0) : ?Event
	{
		if( $post_id == 0 ) return new self();
		$post = get_post($post_id);
		self::find_by_post($post);
		return $post ? self::find_by_post($post) : null;
	}

	public static function find_by_post(?WP_Post $post) : ?Event
	{
		if(!$post) return null;
		$instance = new self();
		$instance->load_postdata($post);
		return $instance;
	}

	public function get_rest_fields() : array {
		return [
			'id' => $this->post_id,
			'link' => get_permalink($this->post_id),
			'image' => $this->get_image(),
			'titel' => $this->event_name,
			'has_coupons' => \EM_Coupons::event_has_coupons($this),
			'price' => new \Contexis\Events\Intl\Price($this->get_price()),
			'is_free' => $this->is_free(),
			'start' => $this->event_start->getTimestamp(),
			'end' => $this->event_end->getTimestamp(),
			'is_single_day' => $this->event_start == $this->event_end,
			'audience' => $this->event_audience,
			'excerpt' => $this->post_excerpt,
			'allow_donation' => get_metadata('post', $this->post_id, '_event_rsvp_donation', true) == "1",
			'booking_start' => get_post_meta($this->post_id, '_event_rsvp_start', true),
			'booking_end' => get_post_meta($this->post_id, '_event_rsvp_end', true),
		];
	}

	public function get_image() {
		$thumbnail = get_post_thumbnail_id($this->post_id);
		if(!$thumbnail) return false;
		$attachment = [
			'attachment_id' => $thumbnail,
			'sizes' => []
		];
		
		foreach(get_intermediate_image_sizes($thumbnail) as $size) {
			$attachment['sizes'][$size] = array_combine(['url', 'width',  'height', 'resized'], wp_get_attachment_image_src( $thumbnail, $size) );
		}
	
		return $attachment;
		
	}
	
	public function start(): DateTime {
		return $this->event_start ??= new DateTime();
	}

	public function end(): DateTime {
		return $this->event_end ??= new DateTime();
	}

	function load_postdata(WP_Post $event_post)
	{
		if( $event_post->post_type != RecurringEventPost::POST_TYPE && $event_post->post_type != EventPost::POST_TYPE ){
			return false;
		}
		$this->event_id = $event_post->ID;
		$this->post_id = absint($event_post->ID);
		$this->event_name = $event_post->post_title;
		$this->event_owner = $event_post->post_author;
		$this->post_content = $event_post->post_content;
		$this->post_excerpt = $event_post->post_excerpt;
		$this->event_slug = $event_post->post_name;
		
		$this->event_date_created = $event_post->post_date;
		$this->event_date_modified = $event_post->post_modified;

		foreach( $event_post as $key => $value ) {
			$this->$key = $value;
		}
			
		$this->get_post_meta();

		if( empty($this->location_id) && !empty($this->event_id) ) $this->location_id = 0; //just set location_id to 0 and avoid any doubt
	}
	
	function get_event_meta(){
		if( empty($this->post_id) ) return array();
		$event_meta = get_post_meta($this->post_id);
		if( !is_array($event_meta) ) $event_meta = array();
		return apply_filters('em_event_get_event_meta', $event_meta);
	}
	
	/**
	 * Retrieve event post meta information via POST, which should be always be called when saving the event custom post via WP.
	 * @return boolean
	 */
	function get_post_meta(){
		$meta = get_post_meta($this->post_id);
		$this->event_timezone = wp_timezone()->getName();

		$this->event_timezone = wp_timezone()->getName();

		$mapping = [
			'event_rsvp' => '_event_rsvp',
			'event_spaces' => '_event_spaces',
			'event_rsvp_donation' => '_event_rsvp_donation',
			'event_all_day' => '_event_all_day',
			'event_audience' => '_event_audience',
			'speaker_id' => '_speaker_id',
		];

		foreach ($mapping as $property => $meta_key) {
			$value = $meta[$meta_key][0] ?? null;

			switch ($meta_key) {
				case '_event_rsvp':
				case '_event_all_day':
					$this->$property = $value == 1;
					break;
				case '_event_spaces':
				case '_event_rsvp_donation':
				case '_speaker_id':
					$this->$property = intval($value);
					break;
				default:
					$this->$property = $value;
			}
		}

		$this->location_id = get_option('dbem_locations_enabled') 
		? intval($meta['_location_id'][0] ?? 0) 
		: 0;

		$this->event_start = isset($meta['_event_start'][0])
		? new \DateTime($meta['_event_start'][0], new \DateTimeZone($this->event_timezone))
		: null;

		$this->event_end = isset($meta['_event_end'][0])
		? new \DateTime($meta['_event_end'][0], new \DateTimeZone($this->event_timezone))
		: null;

		if (!empty($meta['_event_rsvp_start'][0])) {
			$this->event_rsvp_start = new \DateTime($meta['_event_rsvp_start'][0], new \DateTimeZone($this->event_timezone));
		} else {
			$this->event_rsvp_start = get_post($this->post_id)?->post_date 
			? new \DateTime(get_post($this->post_id)->post_date, new \DateTimeZone($this->event_timezone))
			: null;
		}

		if (!empty($meta['_event_rsvp_end'][0])) {
			$this->event_rsvp_end = new \DateTime($meta['_event_rsvp_end'][0], new \DateTimeZone($this->event_timezone));
		} elseif ($this->event_start instanceof \DateTime) {
			$this->event_rsvp_end = clone $this->event_start;
		} else {
			$this->event_rsvp_end = null;
		}
		
		return apply_filters('em_event_get_post_meta', count($this->errors) == 0, $this);
	}

	function save()
	{
		$meta_save = $this->save_meta();
		$result = $meta_save;

		return $result;
	}
	
	function save_meta(){

		//Add/Delete Tickets
		if(!$this->event_rsvp){
			$this->get_bookings()->get_tickets()->delete();
			$this->get_bookings()->delete();
		}elseif( current_user_can('edit_published_posts') ){
			if( !$this->get_bookings()->get_tickets()->save() ){
				$this->add_error( $this->get_bookings()->get_tickets()->get_errors() );
			}
		}

		return apply_filters('em_event_save_meta', count($this->errors) == 0, $this);
	}
	
	/**
	 * Duplicates this event and returns the duplicated event. Will return false if there is a problem with duplication.
	 * @return Event
	 */
	function duplicate(){
		global $wpdb;
		//First, duplicate.
		if( !current_user_can('edit_published_posts') ) return apply_filters('em_event_duplicate', false, $this);
		
		$event = clone $this;
		$event->get_categories(); //before we remove event/post ids
		$event->get_bookings()->get_tickets(); //in case this wasn't loaded and before we reset ids
		$event->event_id = 0;
		$event->post_id = 0;
		$event->post_name = '';
		$event->location_id = $event->location_id ?? 0;
		$event->get_bookings()->event_id = 0;
		$event->get_bookings()->get_tickets()->event_id = 0;
		//if bookings reset ticket ids and duplicate tickets
		foreach($event->get_bookings()->get_tickets()->tickets as $ticket){
			$ticket->ticket_id = 0;
			$ticket->event_id = 0;
		}
		do_action('em_event_duplicate_pre', $event, $this);
		
		if( !$event->save() ) return;
		
		$event->feedback_message = sprintf(__("%s successfully duplicated.", 'events'), __('Event','events'));
	
		//other non-EM post meta inc. featured image
		$event_meta = $this->get_event_meta();
		$new_event_meta = $event->get_event_meta();
		$event_meta_inserts = array();

		foreach($event_meta as $event_meta_key => $event_meta_vals){
			if( $event_meta_key == '_wpas_' ) continue; 
			if( is_array($event_meta_vals) ){
				if( !array_key_exists($event_meta_key, $new_event_meta) &&  !in_array($event_meta_key, array('_event_attributes', '_edit_last', '_edit_lock')) ){
					foreach($event_meta_vals as $event_meta_val){
						$event_meta_inserts[] = "({$event->post_id}, '{$event_meta_key}', '{$event_meta_val}')";
					}
				}
			}
		}
		//save in one SQL statement
		if( !empty($event_meta_inserts) ){
			$wpdb->query('INSERT INTO '.$wpdb->postmeta." (post_id, meta_key, meta_value) VALUES ".implode(', ', $event_meta_inserts));
		}
		if( array_key_exists('_event_approvals_count', $event_meta) ) update_post_meta($event->post_id, '_event_approvals_count', 0);
		$wpdb->query('INSERT INTO '.EM_META_TABLE." (object_id, meta_key, meta_value) SELECT '{$event->event_id}', meta_key, meta_value FROM ".EM_META_TABLE." WHERE object_id='{$this->event_id}'");
		return apply_filters('em_event_duplicate', $event, $this);		
	}
	
	function duplicate_url($raw = false){
	    $url = add_query_arg(array('action'=>'event_duplicate', 'event_id'=>$this->event_id, '_wpnonce'=> wp_create_nonce('event_duplicate_'.$this->event_id)));
	    $url = apply_filters('em_event_duplicate_url', $url, $this);
	    $url = $raw ? esc_url_raw($url):esc_url($url);
	    return $url;
	}
	
	/**
	 * Delete whole event, including bookings, tickets, etc.
	 * @param boolean $force_delete
	 * @return boolean
	 */
	function delete( $force_delete = false ){
		if(!current_user_can('delete_posts')) return;

		do_action('em_event_delete_pre', $this);
		if( $force_delete ){
			$result = wp_delete_post($this->post_id,$force_delete);
		}else{
			$result = wp_trash_post($this->post_id);
		}
		if( !$result && !empty($this->orphaned_event) ){
			//this is an orphaned event, so the wp delete posts would have never worked, so we just delete the row in our events table
			$result = $this->delete_meta();
		}
		
		return apply_filters('em_event_delete', $result != false, $this);
	}
	
	function delete_meta(){
		$this->get_bookings()->delete();
		$this->get_bookings()->get_tickets()->delete();
		
		//Delete the recurrences then this recurrence event
		if( $this->is_recurring() ){
			$result = $this->delete_events(); //was true at this point, so false if fails
		}
		
		return apply_filters('em_event_delete_meta', $result !== false, $this);
	}
	
	public function get_timezone(){
		return $this->start()->getTimezone();
	}
	
	function is_published(){
		return apply_filters('em_event_is_published', ($this->post_status == 'publish' || $this->post_status == 'private'), $this);
	}
	
	/**
	 * Returns a DateTime representation of when bookings close in local event timezone. If no valid date defined, event start date/time will be used.
	 * @return DateTime
	 */
	public function get_rsvp_end(): DateTime
	{
		return $this->event_rsvp_end ??= $this->start();
	}

	public function get_rsvp_start() : DateTime
	{ 		
		return $this->event_rsvp_start ??= new DateTime($this->post_date, new \DateTimeZone($this->event_timezone));
	}
	
	function get_categories() {
		$this->categories = get_the_terms($this->post_id, "event-category");
		return $this->categories;
	}
	
	function get_location() {
		if( !is_object($this->location) || $this->location->location_id != $this->location_id ){
			$this->location = apply_filters('em_event_get_location', 
			Location::get_by_id($this->location_id));
		}
		return $this->location;
	}
	
	/**
	 * Returns whether this event has a phyisical location assigned to it.
	 * @return bool
	 */
	public function has_location(){
		return $this->location_id > 0;
	}

	public function can_book() : bool
	{
		if(!$this->event_rsvp) {
			return false;
		}
		if( $this->get_bookings()->get_spaces() <= 0 ) {
			return false;
		}
		if( !$this->booking_has_started()) {
			return false;
		}
		if( $this->booking_has_ended()) {
			return false;
		}
		if( $this->get_bookings()->get_available_spaces() == 0 ) return false;
		
		return apply_filters('em_event_can_book', true, $this);
	}

	public function no_booking_reason() : string
	{
		if(!$this->event_rsvp) {
			return __("Booking for this event is disabled", "events");
		}
		if( $this->get_bookings()->get_spaces() <= 0 ) {
			return __("No spaces left for this event", "events");
		}
		if( !$this->booking_has_started()) {
			return __("Booking has not started yet", "events");
		}
		if( $this->booking_has_ended()) {
			return __("Booking has ended", "events");
		}
		if( $this->get_bookings()->get_available_spaces() == 0 ) return __("No spaces left for this event", "events");
		return "";
	}
	
	function booking_has_started() 
	{
		$now = new DateTime();
		$start = $this->get_rsvp_start();
		return $start->getTimestamp() < $now->getTimestamp();
	}

	function booking_has_ended()
	{
		$now = new DateTime();
		$end = $this->get_rsvp_end();
		return $end->getTimestamp() < $now->getTimestamp();
	}

	
	function get_contact() : WP_User 
	{	
		if( !is_object($this->contact) ){
			$this->contact = get_userdata($this->event_owner);
		}
		return $this->contact ?: new WP_User(); // Falls der User nicht existiert
	}
	

	function get_bookings( $force_reload = false ) : BookingCollection {
		if( get_option('dbem_rsvp_enabled') ){
			if( (!$this->bookings || $force_reload) ){
				$this->bookings = BookingCollection::from_event($this);
			}
			$this->bookings->event_id = $this->event_id;
		}else{
			return new BookingCollection;
		}
		
		
		return $this->bookings;
	}
	
	/* 
	 * Extends the default EM_Object function by switching blogs as needed if in MS Global mode  
	 * @param string $size
	 * @return string
	 * @see EM_Object::get_image_url()
	 */
	function get_image_url($size = 'full'){
		if( empty($this->post_id) ) return false;
		$event_link = get_post_meta($this->post_id, '_thumbnail_id', true);
		if( empty($event_link) ) return false;
		$event_link = wp_get_attachment_image_src($event_link, $size);
		if( empty($event_link) ) return false;
		$event_link = $event_link[0];
		if( empty($event_link) ) return false;
		$event_link = apply_filters('em_event_get_image_url', $event_link, $this);
		return $event_link;
	}
	
	
	function get_edit_url(){
		if(!current_user_can('edit_published_posts')) return '';
		return admin_url()."post.php?post={$this->post_id}&action=edit";
	}
	
	function get_bookings_url(){
		return is_admin() ? EventPost::get_admin_url(). "&page=events-bookings&event_id=".$this->event_id : '';
	}
	
	function get_permalink(){
		if( empty($event_link) ){
			$event_link = get_post_permalink($this->post_id);
		}
		return apply_filters('em_event_get_permalink', $event_link, $this);
	}
	
	
	
	function is_free( $now = false ){
		return $this->get_price() == 0;
	}

	function get_price(){
		$price = 0;
		
		foreach($this->get_bookings()->get_tickets() as $ticket){
			if( $ticket->get_price() > 0 ){	
				
				$price = $ticket->get_price();
			}
		}

		
		return apply_filters('em_event_get_price',$price, $this);
	}

	function get_formatted_price(){
		$price = $this->get_price();
		return new Price($price);
	}
	
	function output ( $format = '', $target = 'html' ){
		return EventView::render($this, $format, $target);
	}
	
	
	/**
	 * Returns true if this is a recurring event.
	 * @return boolean
	 */
	function is_recurring(){
		return $this->post_type == 'event-recurring' && get_option('dbem_recurrence_enabled');
	}	
	/**
	 * Will return true if this individual event is part of a set of events that recur
	 * @return boolean
	 */
	function is_recurrence(){
		return ( $this->recurrence_id > 0 && get_option('dbem_recurrence_enabled') );
	}
	/**
	 * Returns if this is an individual event and is not a recurrence
	 * @return boolean
	 */
	function is_individual(){
		return ( !$this->is_recurring() && !$this->is_recurrence() );
	}

}