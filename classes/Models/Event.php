<?php

namespace Contexis\Events\Models;

use \Contexis\Events\PostTypes\EventPost;
use Contexis\Events\Intl\Price;
use Contexis\Events\Models\Location;
use DateTime;
use Contexis\Events\Collections\BookingCollection;
use Contexis\Events\Collections\CouponCollection;
use Contexis\Events\Collections\TicketCollection;
use Contexis\Events\Views\EventView;
use Contexis\Events\PostTypes\RecurringEventPost;
use WP_Post;
use WP_User;
use Contexis\Events\Core\Contracts\Model;
use Contexis\Events\Repositories\BookingRepository;
use Contexis\Events\Core\Utilities\Image;
use JsonSerializable;

class Event implements JsonSerializable { 

	public int $event_id = 0;
	public string $event_slug;
	public int $event_owner = 0;
	public string $event_name = "";
	public int $location_id = 0;
	
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
	public string $event_date_modified;
	public string $event_date_created;
	public int $event_spaces = 0;
	public ?EventSpaces $spaces = null;
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
	
	var $post_title;
	var $post_excerpt = '';
	public string $post_status;
	var $post_name;
	var $post_content;
	private DateTime $post_date;
	
	var $post_type;
	var $filter;

	public function __construct() {
		$this->spaces = new EventSpaces($this->event_id);
	}
	/**
	 * When cloning this event, we get rid of the bookings and location objects, since they can be retrieved again from the cache instead. 
	 */
	public function __clone(){
		$this->bookings = null;
		$this->location = null;
	}

	public static function get_by_id(int $event_id) : ?Event
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

	public function jsonSerialize(): mixed
	{
		if( empty($this->post_id) ) {
			return [];
		}
		return [
			'id' => $this->post_id,
			'link' => get_permalink($this->post_id),
			'title' => $this->event_name,
			'has_coupons' => CouponCollection::from_event($this)->count() > 0,
			'price' => new \Contexis\Events\Intl\Price($this->get_price()),
			'image' => Image::from_post_id($this->post_id),
			'is_free' => $this->is_free(),
			'start' => $this->event_start->format(DATE_ATOM),
			'end' => $this->event_end->format(DATE_ATOM),
			'is_single_day' => $this->event_start == $this->event_end,
			'audience' => $this->event_audience,
			'excerpt' => $this->post_excerpt,
			'allow_donation' => get_metadata('post', $this->post_id, '_event_rsvp_donation', true) == "1",
			'booking_start' => $this->event_rsvp_start->format(DATE_ATOM),
			'booking_end' => $this->event_rsvp_end->format(DATE_ATOM),
			'spaces' => $this->spaces->jsonSerialize(),
			'location' => $this->get_location()->jsonSerialize(),
		];
	}
	
	public function start(): DateTime {
		return $this->event_start ??= new DateTime();
	}

	public function end(): DateTime {
		return $this->event_end ??= new DateTime();
	}

	public function get_rsvp_end(): DateTime
	{
		return $this->event_rsvp_end ??= $this->start();
	}

	public function get_rsvp_start() : DateTime
	{ 		
		return $this->event_rsvp_start ??= $this->post_date;
	}

	public function get_image() : string {
		return '';
	}

	public function get_coupon_ids() : array {
		$coupons = get_post_meta($this->event_id, '_event_coupons', true);
		if (!is_array($coupons)) return [];
		$coupons = array_filter(array_map('intval', $coupons));
		return $coupons;
	}

	function load_postdata(WP_Post $event_post) : void
	{
		if( $event_post->post_type != RecurringEventPost::POST_TYPE && $event_post->post_type != EventPost::POST_TYPE ){
			return;
		}

		$this->event_id = $event_post->ID;
		$this->post_id = absint($event_post->ID);
		$this->event_name = $event_post->post_title;
		$this->event_owner = $event_post->post_author;
		$this->post_content = $event_post->post_content;
		$this->post_excerpt = $event_post->post_excerpt;
		$this->event_slug = $event_post->post_name;
		$this->post_date = new DateTime($event_post->post_date, new \DateTimeZone(wp_timezone()->getName()));
			
		$this->get_post_meta();

		if( empty($this->location_id) && !empty($this->event_id) ) $this->location_id = 0; //just set location_id to 0 and avoid any doubt
	}
	
	function get_event_meta() : array {
		if( empty($this->post_id) ) return array();
		$event_meta = get_post_meta($this->post_id);
		if( !is_array($event_meta) ) $event_meta = array();
		return apply_filters('em_event_get_event_meta', $event_meta);
	}
	
	function get_tickets() : TicketCollection {
		return TicketCollection::find_by_event_id($this->event_id);
	}

	function get_available_tickets() : TicketCollection {
		return TicketCollection::find_by_event_id($this->event_id)->get_available();
	}
	
	function get_post_meta() : bool {
		$meta = get_post_meta($this->post_id);
		$this->event_timezone = wp_timezone()->getName();
		$this->event_timezone = wp_timezone()->getName();
		$this->spaces = new EventSpaces($this->event_id);

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
				case '_event_audience':
					$this->$property = is_string($value) ? $value : '';
					break;
				default:
					$this->$property = $value;
			}
		}

		$this->location_id = get_option('dbem_locations_enabled', 1) 
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
		} elseif ($this->event_start) {
			$this->event_rsvp_end = clone $this->event_start;
		} else {
			$this->event_rsvp_end = new \DateTime('now', new \DateTimeZone($this->event_timezone));
		}

	
		return apply_filters('em_event_get_post_meta', count($this->errors) == 0, $this);
	}

	function save()
	{
		return $this->save_meta();
	}
	
	function save_meta() : bool {
		return apply_filters('em_event_save_meta', count($this->errors) == 0, $this);
	}
	
	function duplicate() {
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
		return apply_filters('em_event_duplicate', $event, $this);		
	}
	
	function duplicate_url(bool $raw = false) : string {
	    $url = add_query_arg(array('action'=>'event_duplicate', 'event_id'=>$this->event_id, '_wpnonce'=> wp_create_nonce('event_duplicate_'.$this->event_id)));
	    $url = apply_filters('em_event_duplicate_url', $url, $this);
	    $url = $raw ? esc_url_raw($url):esc_url($url);
	    return $url;
	}
	
	function delete( $force_delete = false ){
		if(!current_user_can('delete_posts')) return;

		do_action('em_event_delete_pre', $this);
		if( $force_delete ){
			$result = wp_delete_post($this->post_id,$force_delete);
		}else{
			$result = wp_trash_post($this->post_id);
		}
		
		return apply_filters('em_event_delete', $result != false, $this);
	}
	

	public function get_timezone(){
		return $this->start()->getTimezone();
	}
	
	function is_published(){
		return apply_filters('em_event_is_published', ($this->post_status == 'publish' || $this->post_status == 'private'), $this);
	}
	
	
	
	function get_categories() {
		$this->categories = get_the_terms($this->post_id, "event-category");
		return $this->categories;
	}
	
	function get_location() : Location {
		if( !is_object($this->location) || $this->location->location_id != $this->location_id ){
			$this->location = apply_filters('em_event_get_location', 
			Location::get_by_id($this->location_id));
		}
		return $this->location;
	}
	
	public function has_location() : bool {
		return $this->location_id > 0;
	}

	public function can_book() : bool
	{
		if(!$this->event_rsvp) {
			return false;
		}
		if( $this->spaces->capacity() <= 0 ) {
			return false;
		}
		if( !$this->booking_has_started()) {
			return false;
		}
		if( $this->booking_has_ended()) {
			return false;
		}
		if( $this->spaces->available() == 0 ) return false;
		
		return apply_filters('em_event_can_book', true, $this);
	}

	public function no_booking_reason() : string
	{
		if(!$this->event_rsvp) {
			return __("Booking for this event is disabled", "events");
		}
		if( $this->spaces->capacity() <= 0 ) {
			return __("No spaces left for this event", "events");
		}
		if( !$this->booking_has_started()) {
			return __("Booking has not started yet", "events");
		}
		if( $this->booking_has_ended()) {
			return __("Booking has ended", "events");
		}
		if( $this->spaces->available() == 0 ) return __("No spaces left for this event", "events");
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

	function get_spaces() : int {
		$tickets = TicketCollection::find_by_event_id($this->event_id);
		if( empty($tickets) ) return 0; //no tickets, no spaces
		$spaces = 0;
		foreach($tickets as $ticket){
			$spaces += $ticket->ticket_spaces;
		}
		return apply_filters('em_event_get_spaces', $spaces, $this);
	}
	
	function get_edit_url(){
		if(!current_user_can('edit_published_posts')) return '';
		return admin_url()."post.php?post={$this->post_id}&action=edit";
	}

	function get_bookings_url(){
		return is_admin() ? EventPost::get_admin_url(). "&page=events-bookings&event_id=".$this->event_id : '';
	}
	
	function is_free( $now = false ) : bool {
		return $this->get_price() == 0;
	}

	function get_price(){
		$price = 0;
		
		foreach(TicketCollection::find_by_event_id($this->event_id) as $ticket){
			if( $ticket->ticket_price > 0 ){	
				$price = $ticket->ticket_price;
			}
		}

		
		return apply_filters('em_event_get_price',$price, $this);
	}

	function get_formatted_price(){
		$price = $this->get_price();
		return new Price($price);
	}
	
	function render ( $format = '', $target = 'html' ){
		return EventView::render($this, $format, $target);
	}
	
	function is_recurring(){
		return $this->post_type == 'event-recurring' && get_option('dbem_recurrence_enabled');
	}	

	function is_recurrence(){
		return ( $this->recurrence_id > 0 && get_option('dbem_recurrence_enabled') );
	}

	function is_individual(){
		return ( !$this->is_recurring() && !$this->is_recurrence() );
	}

}