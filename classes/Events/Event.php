<?php


use \Contexis\Events\EventPost;

class EM_Event extends \EM_Object{ 

	const POST_TYPE = "event";
	/* Field Names */
	var $event_id;
	var $post_id;
	var $event_parent;
	var $event_slug;
	var $event_owner;
	var $event_name;
	var $event_image;
	var $coupon_ids;
	var $coupons;
	var $coupons_count;
	
	protected $event_start_time = '00:00:00';
	protected $event_end_time = '00:00:00';
	protected $event_start_date;
	protected $event_end_date;
	protected $event_start;
	protected $event_end;
	var $event_all_day;
	protected $event_timezone;
	var $post_content;
	var $event_rsvp = 0;
	var $event_rsvp_start;
	var $event_rsvp_end;

	var $event_rsvp_donation;

	var $event_rsvp_spaces;
	var $event_spaces;
	var $event_private;
	var $location_id;
	
	var $recurrence_id;
	var $event_status;
	var $blog_id = 0;
	var $group_id;
	var $event_translation = 0;
	/**
	 * Populated with the non-hidden event post custom fields (i.e. not starting with _) 
	 * @var array
	 */
	var $event_attributes = array();
	/* Recurring Specific Values */
	var $recurrence = 0;
	var $recurrence_interval;
	var $recurrence_freq;
	var $recurrence_byday;
	var $recurrence_days = 0;
	var $recurrence_byweekno;
	var $recurrence_rsvp_days;

	/* new attributes */
	var $event_audience = "";
	var int $speaker_id = 0;
	
	public array $fields = array(
		'event_id' => array( 'name'=>'id', 'type'=>'%d' ),
		'post_id' => array( 'name'=>'post_id', 'type'=>'%d' ),
		'event_parent' => array( 'type'=>'%d', 'null'=>true ),
		'event_slug' => array( 'name'=>'slug', 'type'=>'%s', 'null'=>true ),
		'event_owner' => array( 'name'=>'owner', 'type'=>'%d', 'null'=>true ),
		'event_name' => array( 'name'=>'name', 'type'=>'%s', 'null'=>true ),
		'event_timezone' => array('type'=>'%s', 'null'=>true ),
		'event_start_time' => array( 'name'=>'start_time', 'type'=>'%s', 'null'=>true ),
		'event_end_time' => array( 'name'=>'end_time', 'type'=>'%s', 'null'=>true ),
		'event_start' => array('type'=>'%s', 'null'=>true ),
		'event_end' => array('type'=>'%s', 'null'=>true ),
		'event_all_day' => array( 'name'=>'all_day', 'type'=>'%d', 'null'=>true ),
		'event_start_date' => array( 'name'=>'start_date', 'type'=>'%s', 'null'=>true ),
		'event_end_date' => array( 'name'=>'end_date', 'type'=>'%s', 'null'=>true ),
		'post_content' => array( 'name'=>'notes', 'type'=>'%s', 'null'=>true ),
		'event_rsvp' => array( 'name'=>'rsvp', 'type'=>'%d' ),
		'event_rsvp_end' => array( 'name'=>'rsvp_time', 'type'=>'%s', 'null'=>true ),
		'event_rsvp_start' => array( 'name'=>'rsvp_start', 'type'=>'%s', 'null'=>true ),
		'event_rsvp_spaces' => array( 'name'=>'rsvp_spaces', 'type'=>'%d', 'null'=>true ),
		'event_spaces' => array( 'name'=>'spaces', 'type'=>'%d', 'null'=>true),
		'location_id' => array( 'name'=>'location_id', 'type'=>'%d', 'null'=>true ),
		'recurrence_id' => array( 'name'=>'recurrence_id', 'type'=>'%d', 'null'=>true ),
		'event_status' => array( 'name'=>'status', 'type'=>'%d', 'null'=>true ),
		'event_private' => array( 'name'=>'status', 'type'=>'%d', 'null'=>true ),
		'blog_id' => array( 'name'=>'blog_id', 'type'=>'%d', 'null'=>true ),
		'group_id' => array( 'name'=>'group_id', 'type'=>'%d', 'null'=>true ),
		'event_translation' => array( 'type'=>'%d'),
		'recurrence' => array( 'name'=>'recurrence', 'type'=>'%d', 'null'=>false ), //is this a recurring event template
		'recurrence_interval' => array( 'name'=>'interval', 'type'=>'%d', 'null'=>true ), //every x day(s)/week(s)/month(s)
		'recurrence_freq' => array( 'name'=>'freq', 'type'=>'%s', 'null'=>true ), //daily,weekly,monthly?
		'recurrence_days' => array( 'name'=>'days', 'type'=>'%d', 'null'=>true ), //each event spans x days
		'recurrence_byday' => array( 'name'=>'byday', 'type'=>'%s', 'null'=>true ), //if weekly or monthly, what days of the week?
		'recurrence_byweekno' => array( 'name'=>'byweekno', 'type'=>'%d', 'null'=>true ), //if monthly which week (-1 is last)
		'recurrence_rsvp_days' => array( 'name'=>'recurrence_rsvp_days', 'type'=>'%d', 'null'=>true ), //days before or after start date to generat bookings cut-off date
	);
	var $post_fields = array('event_slug','event_owner','event_name','event_private','event_status','event_attributes','post_id','post_content'); 
	var $recurrence_fields = array('recurrence', 'recurrence_interval', 'recurrence_freq', 'recurrence_days', 'recurrence_byday', 'recurrence_byweekno', 'recurrence_rsvp_days');
	var $image_url = '';
	var $rsvp_end = null;
	var $rsvp_start = null;
	var $location;
	var $bookings;
	var $contact;
	var $categories;
	var $tags;
	var array $errors = array();
	public string $feedback_message;
	var $warnings;
	public array $required_fields = array('event_name', 'event_start_date');
	var $previous_status = false;
	var $recurring_reschedule = false;
	var $recurring_recreate_bookings;
	var $recurring_delete_bookings = false;
	var $just_added_event = false;
	
	var $ID;
	var $post_date;
	var $post_date_gmt;
	var $post_title;
	var $post_excerpt = '';
	var $post_status;
	var $post_name;
	
	var $post_modified;
	var $post_modified_gmt;
	var $post_type;
	var $filter;
	var $status_array;
	
	function __construct() {
		$this->status_array = array(
			0 => __('Pending','events-manager'),
			1 => __('Approved','events-manager')
		);
	}
	
	
	function __get( $var ){
		if(!isset($this->$var))	return null; 

		if( $var == 'event_date_modified' || $var == 'event_date_created'){
	        global $wpdb;
	        $row = $wpdb->get_row($wpdb->prepare("SELECT event_date_created, event_date_modified FROM ".EM_EVENTS_TABLE.' WHERE event_id=%s', $this->event_id));
	        if( $row ){
	            $this->event_date_modified = $row->event_date_modified;
	            $this->event_date_created = $row->event_date_created;
	            return $this->$var;
	        }
	    } elseif ( $var == 'event_timezone' ){
	    	return $this->get_timezone()->getName();
	    }
	    
	    return $this->$var;
	}
	
	public function __set( $prop, $val ){
		if( $prop == 'event_start_date' || $prop == 'event_end_date' ){
			//if date is valid, set it, if not set it to null
			$this->$prop = preg_match('/^\d{4}-\d{2}-\d{2}$/', $val) ? $val : null;
			if( $prop == 'event_start_date') $this->event_start = $this->start()->getTimestamp();
			elseif( $prop == 'event_end_date') $this->event_end = $this->end()->getTimestamp();
			elseif( $prop == 'event_rsvp_end') $this->rsvp_end = null;
		}elseif( $prop == 'event_start_time' || $prop == 'event_end_time' ){
			$this->$prop = preg_match('/^\d{2}:\d{2}:\d{2}$/', $val) ? $val : '00:00:00';
		}
		//deprecated properties, use start()->setTimestamp() instead
		elseif( $prop == 'start' || $prop == 'end' || $prop == 'rsvp_end' ){
			if( is_numeric($val) ){
				$this->$prop()->setTimestamp( (int) $val);
			}elseif( is_string($val) ){
				$this->$val = new EM_DateTime($val, $this->event_timezone);
			}
		}
		//anything else
		else{
			$this->$prop = $val;
		}
		
	}
	
	public function __isset( $prop ){
		if( in_array($prop, array('event_start_date', 'event_end_date', 'event_start_time', 'event_end_time', 'event_rsvp_start', 'event_rsvp_end', 'event_start', 'event_end')) ){
			return !empty($this->$prop);
		}elseif( $prop == 'event_timezone' ){
			return true;
		}elseif( $prop == 'start' || $prop == 'end' || $prop == 'rsvp_end' ){
			return $this->$prop()->valid;
		}
		return isset($this->$prop);
	}
	
	/**
	 * When cloning this event, we get rid of the bookings and location objects, since they can be retrieved again from the cache instead. 
	 */
	public function __clone(){
		$this->bookings = null;
		$this->location = null;
	}

	public static function find_by_event_id(int $event_id) : ?EM_Event
	{
		global $wpdb;
		$post_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM ".EM_EVENTS_TABLE." WHERE event_id = %d", $event_id));
		return $post_id ? self::find_by_post_id($post_id) : null;
	}

	public static function find_by_post_id(int $post_id = 0) : ?EM_Event
	{
		if( $post_id == 0 ) return new self();
		$post = get_post($post_id);
		self::find_by_post($post);
		return $post ? self::find_by_post($post) : null;
	}

	public static function find_by_post(WP_Post $post) : ?EM_Event
	{
		$instance = new self();
		$instance->load_postdata($post);
		return $instance;
	}

	

	
	public function get_tickets_rest() {
		$tickets = (array)$this->get_bookings()->get_available_tickets()->tickets;
		$ticket_collection = [];
		$fields = [];
		$raw_fields = \EM_Attendees_Form::get_attendee_form($this->post_id);
		foreach($raw_fields as $field) {
			$fields[$field['fieldid']] = '';
		}

		
        foreach($tickets as $id => $ticket) {
			$ticket_collection[$id]= [
                "id" => $id,
                "event_id" => $ticket->event_id,
                "is_available" => $ticket->is_available,
                "max" => intval($ticket->ticket_max ? min( $ticket->ticket_spaces, $ticket->ticket_max ) : $ticket->ticket_spaces),
                "price" => floatval($ticket->ticket_price),
                "min" => $ticket->ticket_min ?: 0,
                "name" => $ticket->ticket_name,
                "description" => $ticket->ticket_description,
				"fields" => $fields
            ];
        }
        return $ticket_collection;
	}

	private function get_image() {

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
	
	function load_postdata(WP_Post $event_post)
	{
		
		if( is_object($event_post) && ($event_post->post_type == 'event-recurring' || $event_post->post_type == EM_POST_TYPE_EVENT) ){
			//load post data - regardless
			$this->post_id = absint($event_post->ID);
			$this->event_name = $event_post->post_title;
			$this->event_owner = $event_post->post_author;
			$this->post_content = $event_post->post_content;
			$this->post_excerpt = $event_post->post_excerpt;
			$this->event_slug = $event_post->post_name;
			$this->event_image = $this->get_image();

			foreach( $event_post as $key => $value ){ //merge post object into this object
				$this->$key = $value;
			}

			$this->recurrence = $this->is_recurring() ? 1:0;
			//load meta data and other related information
			if( $event_post->post_status != 'auto-draft' ){
			    $event_meta = $this->get_event_meta();				
				foreach($event_meta as $event_meta_key => $event_meta_val){
					
					$field_name = substr($event_meta_key, 1);
					if($event_meta_key[0] != '_'){
						$this->event_attributes[$event_meta_key] = ( is_array($event_meta_val) ) ? $event_meta_val[0]:$event_meta_val;
					}elseif( is_string($field_name) && !in_array($field_name, $this->post_fields) ){
						if( array_key_exists($field_name, $this->fields) ){
							$this->$field_name = $event_meta_val[0];
						}
					}
				}

				$this->event_rsvp_donation = get_post_meta($this->post_id, '_event_rsvp_donation', true) == 1 ? true : false;
				$this->speaker_id = array_key_exists('_speaker_id', $event_meta) ? intval($event_meta['_speaker_id'][0]) : 0;
				
				
				//quick compatability fix in case _event_id isn't loaded or somehow got erased in post meta
				if( empty($this->event_id) && !$this->is_recurring() ){
					global $wpdb;
					
					$event_array = $wpdb->get_row('SELECT * FROM '.EM_EVENTS_TABLE. ' WHERE post_id='.$this->post_id, ARRAY_A);	
					
					if( !empty($event_array['event_id']) ){
						foreach($event_array as $key => $value){
							if( !empty($value) && empty($this->$key) ){
								update_post_meta($event_post->ID, '_'.$key, $value);
								$this->$key = $value;
							}
						}
					}
				}
			}
			$this->get_status();
			//$this->compat_keys();
		}elseif( !empty($this->post_id) ){
			//we have an orphan... show it, so that we can at least remove it on the front-end
			global $wpdb;
			
			$event_array = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".EM_EVENTS_TABLE." WHERE post_id=%d",$this->post_id), ARRAY_A);
			
		    if( is_array($event_array) ){
				$this->orphaned_event = true;
				$this->post_id = $this->ID = $event_array['post_id'] = null; //reset post_id because it doesn't really exist
				$this->from_array($event_array);
		    }
		}
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
		
		do_action('em_event_get_post_meta_pre', $this);
		
		//Check if this is recurring or not early on so we can take appropriate action further down
		$this->recurrence = get_post_type($this->post_id) == 'event-recurring' ? 1:0;
		$this->post_type = $this->recurrence ? 'event-recurring':EM_POST_TYPE_EVENT;
		
		$this->event_timezone = EM_DateTimeZone::create()->getName();
		
		$this->event_start = $this->event_end = null;

		$this->event_rsvp = get_post_meta($this->post_id, '_event_rsvp', true) ? 1:0;
		$this->event_start_date = get_post_meta($this->post_id, '_event_start_date', true) ?? date('Y-m-d')	;
		$this->event_end_date = get_post_meta($this->post_id, '_event_end_date', true) ?? date('Y-m-d')	;
		
		$this->event_rsvp_end = get_post_meta($this->post_id, '_event_rsvp_end', true);
		$this->event_rsvp_start = get_post_meta($this->post_id, '_event_rsvp_start', true);
		$this->event_spaces = get_post_meta($this->post_id, '_event_spaces', true);
		$this->event_rsvp_donation = get_post_meta($this->post_id, '_event_rsvp_donation', true);
		
		$this->event_all_day = get_post_meta($this->post_id, '_event_all_day', true);
		
		$this->event_start_time = $this->event_all_day ? "00:00:00" : get_post_meta($this->post_id, '_event_start_time', true);
		$this->event_end_time = $this->event_all_day ? "23:59:59" : get_post_meta($this->post_id, '_event_end_time', true);
		$this->event_start = $this->start()->getTimestamp();
		$this->event_end = $this->end()->getTimestamp();
		
		//Get Location Info
		$this->location_id = get_option('dbem_locations_enabled') ? get_post_meta($this->post_id, '_location_id', true) : 0;
		
		//group id
		$this->group_id = (!empty($_POST['group_id']) && is_numeric($_POST['group_id'])) ? absint($_POST['group_id']):0;
		
		//Recurrence data
		if( $this->is_recurring() ){
			$this->recurrence = 1; //just in case
			
			$this->recurrence_freq = get_post_meta($this->post_id, '_recurrence_freq', true) ?? 'daily';
			$this->recurrence_interval = get_post_meta($this->post_id, '_recurrence_interval', true) ?? 1;
			if($this->recurrence_interval == 0) $this->recurrence_interval = 1; // prevent loop
			$this->recurrence_byweekno = get_post_meta($this->post_id, '_recurrence_byweekno', true) ?? '';
			$this->recurrence_days = get_post_meta($this->post_id, '_recurrence_days', true) ?? 0;
			$this->recurrence_byday = get_post_meta($this->post_id, '_recurrence_byday', true) ?? null;
		
			
			//here we do a comparison between new and old event data to see if we are to reschedule events or recreate bookings
			if( $this->event_id ){ //only needed if this is an existing event needing rescheduling/recreation
				//Get original recurring event so we can tell whether event recurrences or bookings will be recreated or just modified
				
				$this->recurring_reschedule = $this->check_reschedule();
				
				
				//now check tickets if we don't already have to reschedule
				if( !$this->recurring_reschedule && $this->event_rsvp ){
					//@TODO - ideally tickets could be independent of events, it'd make life easier here for comparison and editing without rescheduling
					$tickets = $this->get_bookings()->get_tickets();
					//we compare tickets
					foreach( $this->get_bookings()->get_tickets()->tickets as $ticket ){
						if( !empty($ticket->ticket_id) && !empty($tickets->tickets[$ticket->ticket_id]) ){
							$new_ticket = $ticket->to_array(true);
							foreach( $tickets->tickets[$ticket->ticket_id]->to_array() as $k => $v ){
								if( !(empty($new_ticket[$k]) && empty($v)) && ((empty($new_ticket[$k]) && $v) || $new_ticket[$k] != $v) ){
									if( $k == 'ticket_meta' && is_array($v) && is_array($new_ticket['ticket_meta']) ){
										foreach( $v as $k_meta => $v_meta ){
											if( (empty($new_ticket['ticket_meta'][$k_meta]) && !empty($v_meta)) || $new_ticket['ticket_meta'][$k_meta] != $v_meta ){
												$this->recurring_recreate_bookings = true; //something changed, so we reschedule
											}
										}
									}else{
										$this->recurring_recreate_bookings = true; //something changed, so we reschedule
									}
								}
							}
						}else{
							$this->recurring_recreate_bookings = true; //we have a new ticket
						}
					}
				}elseif( !empty($deleting_bookings) ){
					$this->recurring_delete_bookings = true;
				}
				unset($EM_Event);
			}else{
				//new event so we create everything from scratch
				$this->recurring_reschedule = $this->recurring_recreate_bookings = true;
			}
			//recurring events may have a cut-off date x days before or after the recurrence start dates
			$this->recurrence_rsvp_days = null;
			
			if( array_key_exists('recurrence_rsvp_days', $_POST) ){
				if( !empty($_POST['recurrence_rsvp_days_when']) && $_POST['recurrence_rsvp_days_when'] == 'after' ){
					$this->recurrence_rsvp_days = absint($_POST['recurrence_rsvp_days']);
				}else{ //by default the start date is the point of reference
					$this->recurrence_rsvp_days = absint($_POST['recurrence_rsvp_days']) * -1;
				}
			}
			
			//create timestamps and set rsvp date/time for a normal event
			if( !is_numeric($this->recurrence_rsvp_days) ){
				//falback in case nothing gets set for rsvp cut-off
				$this->event_rsvp_start = $this->event_rsvp_end = $this->rsvp_end = null;
			}else{
				$this->event_rsvp_start = $this->start()->copy()->modify($this->recurrence_rsvp_days.' days')->getDate();
			}
		}else{
			foreach( $this->recurrence_fields as $recurrence_field ){
				$this->$recurrence_field = null;
			}
			$this->recurrence = 0; // to avoid any doubt
		}

		//$this->compat_keys(); //compatability

		$this->event_audience = get_post_meta($this->post_id, '_event_audience', true);
		
		$this->speaker_id = intval(get_post_meta($this->post_id, '_speaker_id', true)) ?? 0	;

		return apply_filters('em_event_get_post_meta', count($this->errors) == 0, $this);

	}

	function check_reschedule() {
		global $wpdb;
		$result = $wpdb->get_row( $wpdb->prepare("SELECT * FROM ". EM_EVENTS_TABLE ." WHERE event_id=%d", $this->event_id) );
		if(!$result) return true;

	//first check event times
		$recurring_event_dates = array(
				'event_start_date' => $result->event_start_date,
				'event_end_date' => $result->event_end_date,
				'event_start_time' => $result->event_start_time,
				'event_end_time' => $result->event_end_time,
				'recurrence_byday' => $result->recurrence_byday,
				'recurrence_byweekno' => $result->recurrence_byweekno,
				'recurrence_days' => $result->recurrence_days,
				'recurrence_freq' => $result->recurrence_freq,
				'recurrence_interval' => $result->recurrence_interval
		);
		
		$reschedule = false;
		//check previously saved event info compared to current recurrence info to see if we need to reschedule
		foreach($recurring_event_dates as $k => $v){
			if( $this->$k != $v ){
				$reschedule = true; //something changed, so we reschedule
			}
		}

		return $reschedule;
	}
	
	function validate(){
		$validate_post = true;
		if( empty($this->event_name) ){
			$validate_post = false; 
			$this->add_error( sprintf(__("%s is required.", 'events'), __('Event name','events')) );
		}
		//anonymous submissions and guest basic info
		
		$validate_tickets = true; //must pass if we can't validate bookings
		if( $this->can_manage('manage_bookings','manage_others_bookings') ){
		    $validate_tickets = $this->get_bookings()->get_tickets()->validate();
		}
		
		$validate_meta = $this->validate_meta();
		if( $validate_post && $validate_meta && $validate_tickets ){
			//if we're all good, let's save the event
			$this->force_status = 'publish';
		}
		file_put_contents('/var/www/vhosts/kids-team.internal/log/eventerrors.log', print_r(['post' => $validate_post, 'meta' => $validate_meta, 'tickets' => $validate_tickets], true));
		return apply_filters('em_event_validate', $validate_post && $validate_meta && $validate_tickets, $this );		
	}
	
	function validate_meta(){
		if ( !session_id() ) {
			session_start();
		}
		$missing_fields = Array ();
		foreach ( array('event_start_date') as $field ) {
			if ( $this->$field == "") {
				$missing_fields[$field] = $field;
			}
		}
		if( preg_match('/\d{4}-\d{2}-\d{2}/', $this->event_start_date) && preg_match('/\d{4}-\d{2}-\d{2}/', $this->event_end_date) ){
			if( $this->start()->getTimestamp() > $this->end()->getTimestamp() ){
				$this->add_error(__('Events cannot start after they end.','events'));
			}elseif( $this->is_recurring() && $this->recurrence_days == 0 && $this->start()->getTimestamp() > $this->end()->getTimestamp() ){
				$this->add_error(__('Events cannot start after they end.','events').' '.__('For recurring events that end the following day, ensure you make your event last 1 or more days.'));
			}
		}else{
			if( !empty($missing_fields['event_start_date']) ) { unset($missing_fields['event_start_date']); }
			if( !empty($missing_fields['event_end_date']) ) { unset($missing_fields['event_end_date']); }
			$this->add_error(__('Dates must have correct formatting. Please use the date picker provided.','events'));
		}
		if( $this->event_rsvp ){
		    

		}
		if( get_option('dbem_locations_enabled') ){
			if( $this->has_location() ){
				// physical location
				if( empty($this->location_id) && !$this->get_location()->validate() ){
					// new location doesn't validate
					$this->add_error($this->get_location()->get_errors());
				}elseif( !empty($this->location_id) && !$this->get_location()->location_id ){
					// non-existent location selected
					$this->add_error( __('Please select a valid location.', 'events') );
				}
			}
		}
		if ( count($missing_fields) > 0){
			// TODO Create friendly equivelant names for missing fields notice in validation
			$this->add_error( __( 'Missing fields: ', 'events') . implode ( ", ", $missing_fields ) . ". " );
		}
		if ( $this->is_recurring() ){
		    if( $this->event_end_date == "" || $this->event_end_date == $this->event_start_date){
		        $this->add_error( __( 'Since the event is repeated, you must specify an event end date greater than the start date.', 'events'));
		    }
		    if( $this->recurrence_freq == 'weekly' && !preg_match('/^[0-9](,[0-9])*$/',$this->recurrence_byday) ){
		        $this->add_error( __( 'Please specify what days of the week this event should occur on.', 'events'));
		    }
		}
		
		return apply_filters('em_event_validate_meta', count($this->errors) == 0, $this );
	}
	
	/**
	 * Will save the current instance into the database, along with location information if a new one was created and return true if successful, false if not.
	 * Will automatically detect whether it's a new or existing event. 
	 * @return boolean
	 */
	function save()
	{
		global $EM_SAVING_EVENT;
		$EM_SAVING_EVENT = true; //this flag prevents our dashboard save_post hooks from going further
		if( !$this->can_manage('edit_events', 'edit_others_events') && empty($this->event_id) ){
			return apply_filters('em_event_save', false, $this);
		}

		$post_array = array();
		//Deal with updates to an event
		if( !empty($this->post_id) ) $post_array = (array) get_post($this->post_id);

		//Overwrite new post info
		$post_array['post_type'] = ($this->recurrence && get_option('dbem_recurrence_enabled')) ? 'event-recurring':EM_POST_TYPE_EVENT;
		$post_array['post_title'] = $this->event_name;
		$post_array['post_content'] = !empty($this->post_content) ? $this->post_content : '';
		$post_array['post_excerpt'] = $this->post_excerpt;
		
		//decide on post status
		if( empty($this->force_status) ){
			if( count($this->errors) == 0 ){
				$post_array['post_status'] = ( $this->can_manage('publish_events','publish_events') ) ? 'publish':'pending';
			}else{
				$post_array['post_status'] = 'draft';
			}
		}else{
		    $post_array['post_status'] = $this->force_status;
		}
		
		//Save post and continue with meta
		$post_id = wp_insert_post($post_array);
		$post_save = false;
		$meta_save = false;
		if( !is_wp_error($post_id) && !empty($post_id) ){
			$post_save = true;
			//refresh this event with wp post info we'll put into the db
			$post_data = get_post($post_id);
			$this->post_id = $this->ID = $post_id;
			$this->post_type = $post_data->post_type;
			$this->event_slug = $post_data->post_name;
			$this->event_owner = $post_data->post_author;
			$this->post_status = $post_data->post_status;
			$this->get_status();
			$this->get_categories()->event_id = $this->event_id;
			$this->categories->post_id = $this->post_id;
			$this->categories->save();
		
			$meta_save = $this->save_meta();
		}
		$result = $meta_save && $post_save;
		if($result) $this->load_postdata($post_data); //reload post info
		
		update_option('em_last_modified', time());
		
		$EM_SAVING_EVENT = false;

		if(!$result || !$this->is_published()) return $result;
        
        wp_cache_set($this->event_id, $this, 'em_events');
        wp_cache_set($this->post_id, $this->event_id, 'em_events_ids');

		return $result;
	}
	
	function save_meta(){
		global $wpdb;
		
		$this->start();
		$this->end();
		if( !$this->can_manage('edit_events', 'edit_others_events') ) return;
		
		do_action('em_event_save_meta_pre', $this);
		
		//first save location
		if( empty($this->location_id) ) $this->location_id = 0;
		//Update Post Meta
		$current_meta_values = $this->get_event_meta();
		foreach( $this->fields as $key => $field_info ){
			//certain keys will not be saved if not needed, including flags with a 0 value. Older databases using custom WP_Query calls will need to use an array of meta_query items using NOT EXISTS - OR - value=0
			if( !in_array($key, $this->post_fields) && $key != 'event_attributes' ){
				//ignore certain fields and delete if not new
				if( !$this->is_recurring() && in_array($key, $this->recurrence_fields) ) $save_meta_key = false;
				if( !$this->is_recurrence() && $key == 'recurrence_id' ) $save_meta_key = false;
				$ignore_zero_keys = array('group_id', 'event_all_day', 'event_parent', 'event_translation');
				if( in_array($key, $ignore_zero_keys) && empty($this->$key) ) $save_meta_key = false;
				
			}elseif( array_key_exists('_'.$key, $current_meta_values) && $key != 'event_attributes' ){ //we should delete event_attributes, but maybe something else uses it without us knowing
				delete_post_meta($this->post_id, '_'.$key);
			}
		}
		
		
		// make sure we have a valid start and end date
		update_post_meta($this->post_id, '_event_start_local', $this->start()->getDateTime());
		update_post_meta($this->post_id, '_event_end_local', $this->end()->getDateTime());
		update_post_meta($this->post_id, '_event_start', $this->start()->getDateTime());
		update_post_meta($this->post_id, '_event_end', $this->end()->getDateTime());
		
		//sort out event status			
		$result = count($this->errors) == 0;
		$this->get_status();
		$this->event_status = ($result) ? $this->event_status:null; //set status at this point, it's either the current status, or if validation fails, null
		//Save to em_event table
		$event_array = $this->to_array(true);
		unset($event_array['event_id']);
		//decide whether or not event is private at this point
		$event_array['event_private'] = ( $this->post_status == 'private' ) ? 1:0;
		
		
		//check if event truly exists, meaning the event_id is actually a valid event id
		
		//save all the meta
		
		if( empty($this->event_id) || !$this->event_exists() ){
			$this->previous_status = 0; //for sure this was previously status 0
			$this->event_date_created = $event_array['event_date_created'] = current_time('mysql');
			if ( !$wpdb->insert(EM_EVENTS_TABLE, $event_array) ){
				$this->add_error( $wpdb->last_error );
			}else{
				//success, so link the event with the post via an event id meta value for easy retrieval
				$this->event_id = $wpdb->insert_id;
				update_post_meta($this->post_id, '_event_id', $this->event_id);
				$this->feedback_message = sprintf(__('Successfully saved %s','events'),__('Event','events'));
				$this->just_added_event = true; //make an easy hook
				$this->get_bookings()->bookings = array(); //set bookings array to 0 to avoid an extra DB query
				do_action('em_event_save_new', $this);
			} 
		}else{
			$event_array['post_content'] = $this->post_content; //in case the content was removed, which is acceptable
			
			$this->get_previous_status();
			$this->event_date_modified = $event_array['event_date_modified'] = current_time('mysql');
			if ( $wpdb->update(EM_EVENTS_TABLE, $event_array, array('event_id'=>$this->event_id) ) === false ){
				$this->add_error( $wpdb->last_error );			
			}else{
				//Also set the status here if status != previous status
				if( $this->previous_status != $this->get_status() ) $this->set_status($this->get_status());
				$this->feedback_message = sprintf(__('Successfully saved %s','events'),__('Event','events'));
			}
			
		}
		
		
		//Add/Delete Tickets
		if($this->event_rsvp == 0 & !$this->just_added_event){
			$this->get_bookings()->get_tickets()->delete();
			$this->get_bookings()->delete();
		}elseif( $this->can_manage('manage_bookings','manage_others_bookings') ){
			if( !$this->get_bookings()->get_tickets()->save() ){
				$this->add_error( $this->get_bookings()->get_tickets()->get_errors() );
			}
		}
		$result = count($this->errors) == 0;
		//deal with categories
		
		//$this->compat_keys(); //compatability keys, loaded before saving recurrences
		//build recurrences if needed
		if( $this->is_recurring() && $result && ($this->is_published() || $this->post_status == 'future' ) ){ //only save events if recurring event validates and is published or set for future
			global $EM_EVENT_SAVE_POST;
			//If we're in WP Admin and this was called by EM_Event_Post_Admin::save_post, don't save here, it'll be done later in EM_Event_Recurring_Post_Admin::save_post
			if( empty($EM_EVENT_SAVE_POST) ){
				if( $this->just_added_event ) $this->recurring_reschedule = true;
				if( !$this->save_events() ){
					$this->add_error(__ ( 'Something went wrong with the recurrence update...', 'events'). __ ( 'There was a problem saving the recurring events.', 'events'));
				}
			}
		}
		if( !empty($this->just_added_event) ){
			do_action('em_event_added', $this);
		}
		

		return apply_filters('em_event_save_meta', count($this->errors) == 0, $this);
	}


	function event_exists() {
		global $wpdb;
		if (empty($this->event_id)) return false;
		if( !empty($this->orphaned_event ) && !empty($this->post_id) ) return true; 
		return $wpdb->get_var('SELECT post_id FROM '.EM_EVENTS_TABLE." WHERE event_id={$this->event_id}") == $this->post_id;
	}
	
	/**
	 * Duplicates this event and returns the duplicated event. Will return false if there is a problem with duplication.
	 * @return EM_Event
	 */
	function duplicate(){
		global $wpdb;
		//First, duplicate.
		if( !$this->can_manage('edit_events','edit_others_events') ) return apply_filters('em_event_duplicate', false, $this);
		
		$EM_Event = clone $this;
		$EM_Event->get_categories(); //before we remove event/post ids
		$EM_Event->get_bookings()->get_tickets(); //in case this wasn't loaded and before we reset ids
		$EM_Event->event_id = null;
		$EM_Event->post_id = null;
		$EM_Event->ID = null;
		$EM_Event->post_name = '';
		$EM_Event->location_id = $EM_Event->location_id ?? 0;
		$EM_Event->get_bookings()->event_id = null;
		$EM_Event->get_bookings()->get_tickets()->event_id = null;
		//if bookings reset ticket ids and duplicate tickets
		foreach($EM_Event->get_bookings()->get_tickets()->tickets as $ticket){
			$ticket->ticket_id = 0;
			$ticket->event_id = 0;
		}
		do_action('em_event_duplicate_pre', $EM_Event, $this);
		$EM_Event->duplicated = true;
		$EM_Event->force_status = 'draft';
		if( !$EM_Event->save() ) return;
		
		$EM_Event->feedback_message = sprintf(__("%s successfully duplicated.", 'events'), __('Event','events'));
		//save tags here - eventually will be moved into part of $this->save();
		
		$EM_Tags = new EM_Tags($this);
		$EM_Tags->event_id = $EM_Event->event_id;
		$EM_Tags->post_id = $EM_Event->post_id;
		$EM_Tags->save();
	
		//other non-EM post meta inc. featured image
		$event_meta = $this->get_event_meta($this->blog_id);
		$new_event_meta = $EM_Event->get_event_meta($EM_Event->blog_id);
		$event_meta_inserts = array();
		//Get custom fields and post meta - adapted from $this->load_post_meta()
		foreach($event_meta as $event_meta_key => $event_meta_vals){
			if( $event_meta_key == '_wpas_' ) continue; //allow JetPack Publicize to detect this as a new post when published
			if( is_array($event_meta_vals) ){
				if( !array_key_exists($event_meta_key, $new_event_meta) &&  !in_array($event_meta_key, array('_event_attributes', '_edit_last', '_edit_lock')) ){
					foreach($event_meta_vals as $event_meta_val){
						$event_meta_inserts[] = "({$EM_Event->post_id}, '{$event_meta_key}', '{$event_meta_val}')";
					}
				}
			}
		}
		//save in one SQL statement
		if( !empty($event_meta_inserts) ){
			$wpdb->query('INSERT INTO '.$wpdb->postmeta." (post_id, meta_key, meta_value) VALUES ".implode(', ', $event_meta_inserts));
		}
		if( array_key_exists('_event_approvals_count', $event_meta) ) update_post_meta($EM_Event->post_id, '_event_approvals_count', 0);
		//copy anything from the em_meta table too
		$wpdb->query('INSERT INTO '.EM_META_TABLE." (object_id, meta_key, meta_value) SELECT '{$EM_Event->event_id}', meta_key, meta_value FROM ".EM_META_TABLE." WHERE object_id='{$this->event_id}'");
		//set event to draft status
		return apply_filters('em_event_duplicate', $EM_Event, $this);

		//TODO add error notifications for duplication failures.
		
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
		if( $this->can_manage('delete_events', 'delete_others_events') ){
		    if( !is_admin() ){
				include_once('EventPostAdmin.php');
				if( !defined('EM_EVENT_DELETE_INCLUDE') ){
					EM_Event_Post_Admin::init();
					EM_Event_Recurring_Post_Admin::init();
					define('EM_EVENT_DELETE_INCLUDE',true);
				}
		    }
		    do_action('em_event_delete_pre', $this);
			if( $force_delete ){
				$result = wp_delete_post($this->post_id,$force_delete);
			}else{
				$result = wp_trash_post($this->post_id);
				if( !$result && $this->post_status == 'trash' ){
				    //we're probably dealing with a trashed post already, but the event_status is null from < v5.4.1
				    $this->set_status(-1);
				    $result = true;
				}
			}
			if( !$result && !empty($this->orphaned_event) ){
			    //this is an orphaned event, so the wp delete posts would have never worked, so we just delete the row in our events table
				$result = $this->delete_meta();
			}
		}else{
			$result = false;
		}
		return apply_filters('em_event_delete', $result != false, $this);
	}
	
	function delete_meta(){
		global $wpdb;
		$result = false;
		if( $this->can_manage('delete_events', 'delete_others_events') ){
			do_action('em_event_delete_meta_event_pre', $this);
			$result = $wpdb->query ( $wpdb->prepare("DELETE FROM ". EM_EVENTS_TABLE ." WHERE event_id=%d", $this->event_id) );
			if( $result !== false ){
				$this->get_bookings()->delete();
				$this->get_bookings()->get_tickets()->delete();
				
				//Delete the recurrences then this recurrence event
				if( $this->is_recurring() ){
					$result = $this->delete_events(); //was true at this point, so false if fails
				}
			}
		}
		return apply_filters('em_event_delete_meta', $result !== false, $this);
	}
	
	
	/**
	 * Change the status of the event. This will save to the Database too. 
	 * @param int $status 				A number to change the status to, which may be -1 for trash, 1 for publish, 0 for pending or null if draft.
	 * @param boolean $set_post_status 	If set to true the wp_posts table status will also be changed to the new corresponding status.
	 * @return string
	 */
	function set_status($status, $set_post_status = false){
		global $wpdb;
		//decide on what status to set and update wp_posts in the process
		if($status === null){ 
			$set_status='NULL'; //draft post
			if($set_post_status){
				//if the post is trash, don't untrash it!
				$wpdb->update( $wpdb->posts, array( 'post_status' => 'draft' ), array( 'ID' => $this->post_id ) );
			}
			$this->post_status = 'draft'; //set post status in this instance
		}elseif( $status == -1 ){ //trashed post
			$set_status = -1;
			if($set_post_status){
				//set the post status of the location in wp_posts too
				$wpdb->update( $wpdb->posts, array( 'post_status' => $this->post_status ), array( 'ID' => $this->post_id ) );
			}
			$this->post_status = 'trash'; //set post status in this instance
		}else{
			$set_status = $status ? 1:0; //published or pending post
			$post_status = $set_status ? 'publish':'pending';
			if( empty($this->post_name) ){
				//published or pending posts should have a valid post slug
				$slug = sanitize_title($this->post_title);
				$this->post_name = wp_unique_post_slug( $slug, $this->post_id, $post_status, EM_POST_TYPE_EVENT, 0);
				$set_post_name = true;
			}
			if($set_post_status){
				$wpdb->update( $wpdb->posts, array( 'post_status' => $post_status, 'post_name' => $this->post_name ), array( 'ID' => $this->post_id ) );
			}elseif( !empty($set_post_name) ){
				//if we've added a post slug then update wp_posts anyway
				$wpdb->update( $wpdb->posts, array( 'post_name' => $this->post_name ), array( 'ID' => $this->post_id ) );
			}
			$this->post_status = $post_status;
		}
		//save in the wp_em_locations table
		$this->get_previous_status();
		$result = $wpdb->query( $wpdb->prepare("UPDATE ".EM_EVENTS_TABLE." SET event_status=$set_status, event_slug=%s WHERE event_id=%d", array($this->post_name, $this->event_id)) );
		$this->get_status(); //reload status
		return apply_filters('em_event_set_status', $result !== false, $status, $this);
	}
	
	public function set_timezone( $timezone = false ){
		//reset UTC times and objects so they're recreated with local time and new timezone
		$this->event_start = $this->event_end = $this->rsvp_end = null;
		$EM_DateTimeZone = EM_DateTimeZone::create($timezone);
		//modify the timezone string name itself
		$this->event_timezone = $EM_DateTimeZone->getName();
	}
	
	public function get_timezone(){
		return $this->start()->getTimezone();
	}
	
	function is_published(){
		return apply_filters('em_event_is_published', ($this->post_status == 'publish' || $this->post_status == 'private'), $this);
	}
	
	/**
	 * Returns an EM_DateTime object of the event start date/time in local timezone of event.
	 * @param bool $utc_timezone Returns EM_DateTime with UTC timezone if set to true, returns local timezone by default.
	 * @return EM_DateTime
	 * @see EM_Event::get_datetime()
	 */
	public function start( $utc_timezone = false ){
		return apply_filters('em_event_start', $this->get_datetime('start', $utc_timezone), $this);
	}
	
	/**
	 * Returns an EM_DateTime object of the event end date/time in local timezone of event
	 * @param bool $utc_timezone Returns EM_DateTime with UTC timezone if set to true, returns local timezone by default.
	 * @return EM_DateTime
	 * @see EM_Event::get_datetime()
	 */
	public function end( $utc_timezone = false ){
		return apply_filters('em_event_end', $this->get_datetime('end', $utc_timezone), $this);
	}
	
	/**
	 * Returns an EM_DateTime representation of when bookings close in local event timezone. If no valid date defined, event start date/time will be used.
	 * @param bool $utc_timezone Returns EM_DateTime with UTC timezone if set to true, returns local timezone by default.
	 * @return DateTime
	 */
	public function rsvp_end( $utc_timezone = false ) : DateTime{
		if( !empty($this->rsvp_end) ) return $this->rsvp_end;

		if( empty($this->event_rsvp_end) ){
			$this->rsvp_end = $this->start()->copy();
			return $this->rsvp_end;
		}

		try {
			$this->rsvp_end = new DateTime($this->event_rsvp_end);
		} catch (Exception $e) {
			$this->rsvp_end = $this->start()->copy();
		}
		return $this->rsvp_end;
	}

	public function rsvp_start( $utc_timezone = false ) : DateTime
	{ 		
		if( empty($this->event_rsvp_start ) ){ 
			$this->event_rsvp_start = new DateTime($this->post_date);	
			return $this->event_rsvp_start;
		}
		
		if(gettype($this->event_rsvp_start) == 'string'){
			$this->event_rsvp_start = new DateTime($this->event_rsvp_start);
		}

		return $this->event_rsvp_start;
	}

	
	public function get_datetime( $when = 'start', $utc_timezone = false ){

		if( $when != 'start' && $when != 'end') return new EM_DateTime(); //currently only start/end dates are relevant
		//Initialize EM_DateTime if not already initialized, or if previously initialized object is invalid (e.g. draft event with invalid dates being resubmitted)
		$when_date = 'event_'.$when.'_date';
		$when_time = 'event_'.$when.'_time';
	
			/* @var EM_DateTime $EM_DateTime */
		$EM_DateTime = new EM_DateTime(($this->$when_date ?: date('Y-m-d')) . " " . ($this->$when_time ?: '00:00:00'));
		
		//Set to UTC timezone if requested, local by default
		$tz = $utc_timezone ? 'UTC' : $this->event_timezone;
		$EM_DateTime->setTimezone($tz);
		return $EM_DateTime;
	}
	
	function get_status($db = false){
		switch( $this->post_status ){
			case 'private':
				$this->event_private = 1;
				$this->event_status = $status = 1;
				break;
			case 'publish':
				$this->event_private = 0;
				$this->event_status = $status = 1;
				break;
			case 'pending':
				$this->event_private = 0;
				$this->event_status = $status = 0;
				break;
			case 'trash':
				$this->event_private = 0;
				$this->event_status = $status = -1;
				break;
			default: //draft or unknown
				$this->event_private = 0;
				$status = $db ? 'NULL':null;
				$this->event_status = null;
				break;
		}
		return $status;
	}
	
	function get_previous_status( $force = false ){
		global $wpdb;
		if( $this->event_id > 0 && ($this->previous_status === false || $force) ){
			$this->previous_status = $wpdb->get_var('SELECT event_status FROM '.EM_EVENTS_TABLE.' WHERE event_id='.$this->event_id); //get status from db, not post_status, as posts get saved quickly
		}
		return $this->previous_status;
	}
	
	/**
	 * Returns an EM_Categories object of the EM_Event instance.
	 * @return EM_Categories
	 */
	function get_categories() {
		if( empty($this->categories) ){
			$this->categories = new EM_Categories($this);
		}elseif(empty($this->categories->event_id)){
			$this->categories->event_id = $this->event_id;
			$this->categories->post_id = $this->post_id;			
		}
		return apply_filters('em_event_get_categories', $this->categories, $this);
	}
	
	
	/**
	 * Returns the physical location object this event belongs to.
	 * @return EM_Location
	 */
	function get_location() {
		global $EM_Location;
		if( is_object($EM_Location) && $EM_Location->location_id == $this->location_id ){
			$this->location = $EM_Location;
		}else{
			if( !is_object($this->location) || $this->location->location_id != $this->location_id ){
				$this->location = apply_filters('em_event_get_location', EM_Location::get($this->location_id), $this);
			}
		}
		return $this->location;
	}
	
	/**
	 * Returns whether this event has a phyisical location assigned to it.
	 * @return bool
	 */
	public function has_location(){
		return !empty($this->location_id);
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
		$start = $this->rsvp_start();
		return $start->getTimestamp() < $now->getTimestamp();
	}

	function booking_has_ended()
	{
		$now = new DateTime();
		$end = $this->rsvp_end();
		return $end->getTimestamp() < $now->getTimestamp();
	}

	
	function get_contact() : WP_User 
	{	
		if( !is_object($this->contact) ){
			$this->contact = get_userdata($this->event_owner);
		}
		return $this->contact ?: new WP_User(); // Falls der User nicht existiert
	}
	
	/**
	 * Retrieve and save the bookings belonging to instance. If called again will return cached version, set $force_reload to true to create a new EM_Bookings object.
	 * @param boolean $force_reload
	 * @return EM_Bookings
	 */
	function get_bookings( $force_reload = false ){
		if( get_option('dbem_rsvp_enabled') ){
			if( (!$this->bookings || $force_reload) ){
				$this->bookings = new EM_Bookings($this);
			}
			$this->bookings->event_id = $this->event_id; //always refresh event_id
			$this->bookings = apply_filters('em_event_get_bookings', $this->bookings, $this);
		}else{
			return new EM_Bookings();
		}
		//TODO for some reason this returned instance doesn't modify the original, e.g. try $this->get_bookings()->add($EM_Booking) and see how $this->bookings->feedback_message doesn't change
		return $this->bookings;
	}
	
	/* 
	 * Extends the default EM_Object function by switching blogs as needed if in MS Global mode  
	 * @param string $size
	 * @return string
	 * @see EM_Object::get_image_url()
	 */
	function get_image_url($size = 'full'){
		$return = parent::get_image_url($size);
		if( !empty($switch_back) ){ restore_current_blog(); }
		return $return;
	}
	
	
	function get_edit_url(){
		if( $this->can_manage('edit_events','edit_others_events') ){
			
			if( empty($link))
				$link = admin_url()."post.php?post={$this->post_id}&action=edit";
			
			return apply_filters('em_event_get_edit_url', $link, $this);
		}
	}
	
	function get_bookings_url(){
		return is_admin() ? EM_ADMIN_URL. "&page=events-bookings&event_id=".$this->event_id : '';
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
		return new Contexis\Events\Intl\Price($price);
	}
	
	function output ( $format = '', $target = 'html' ){
		return EventView::render($this, $format, $target);
	}
	
	function output_times() {
		return \Contexis\Events\Intl\Date::get_time($this->start()->getTimestamp(), $this->end()->getTimestamp());
	}
	
	function output_dates() {
		return \Contexis\Events\Intl\Date::get_date($this->start()->getTimestamp(), $this->end()->getTimestamp());
	}
	
	/**********************************************************
	 * RECURRENCE METHODS
	 ***********************************************************/
	
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
	
	/**
	 * Gets the event recurrence template, which is an EM_Event object (based off an event-recurring post)
	 * @return EM_Event
	 */
	function get_event_recurrence(){
		if(!$this->is_recurring()){
			return EM_Event::find_by_event_id($this->recurrence_id);
		}else{
			return $this;
		}
	}
	
	function get_detach_url(){
		return admin_url().'admin.php?event_id='.$this->event_id.'&amp;action=event_detach&amp;_wpnonce='.wp_create_nonce('event_detach_'.get_current_user_id().'_'.$this->event_id);
	}
	
	function get_attach_url($recurrence_id){
		return admin_url().'admin.php?undo_id='.$recurrence_id.'&amp;event_id='.$this->event_id.'&amp;action=event_attach&amp;_wpnonce='.wp_create_nonce('event_attach_'.get_current_user_id().'_'.$this->event_id);
	}
	
	/**
	 * Returns if this is an individual event and is not recurring or a recurrence
	 * @return boolean
	 */
	function detach(){
		global $wpdb;
		if( $this->is_recurrence() && !$this->is_recurring() && $this->can_manage('edit_recurring_events','edit_others_recurring_events') ){
			//remove recurrence id from post meta and index table
			$url = $this->get_attach_url($this->recurrence_id);
			$wpdb->update(EM_EVENTS_TABLE, array('recurrence_id'=>null), array('event_id' => $this->event_id));
			delete_post_meta($this->post_id, '_recurrence_id');
			$this->feedback_message = __('Event detached.','events') . ' <a href="'.$url.'">'.__('Undo','events').'</a>';
			$this->recurrence_id = 0;
			return apply_filters('em_event_detach', true, $this);
		}
		$this->add_error(__('Event could not be detached.','events'));
		return apply_filters('em_event_detach', false, $this);
	}
	
	/**
	 * Returns if this is an individual event and is not recurring or a recurrence
	 * @return boolean
	 */
	function attach($recurrence_id){
		global $wpdb;
		if( !$this->is_recurrence() && !$this->is_recurring() && is_numeric($recurrence_id) && $this->can_manage('edit_recurring_events','edit_others_recurring_events') ){
			//add recurrence id to post meta and index table
			$wpdb->update(EM_EVENTS_TABLE, array('recurrence_id'=>$recurrence_id), array('event_id' => $this->event_id));
			update_post_meta($this->post_id, '_recurrence_id', $recurrence_id);
			$this->feedback_message = __('Event re-attached to recurrence.','events');
			return apply_filters('em_event_attach', true, $recurrence_id, $this);
		}
		$this->add_error(__('Event could not be attached.','events'));
		return apply_filters('em_event_attach', false, $recurrence_id, $this);
	}

	
	
	/**
	 * Saves events and replaces old ones. Returns true if sucecssful or false if not.
	 * @return boolean
	 */
	function save_events() {
		global $wpdb;
		
		if( !$this->can_manage('edit_events','edit_others_events') ) return apply_filters('em_event_save_events', false, $this, array(), array());
		$event_ids = $post_ids = $event_dates = $events = array();
		if( $this->is_published() || 'future' == $this->post_status ){
			$result = false;
			//check if there's any events already created, if not (such as when an event is first submitted for approval and then published), force a reschedule.
			if( $wpdb->get_var('SELECT COUNT(event_id) FROM '.EM_EVENTS_TABLE.' WHERE recurrence_id='. absint($this->event_id)) == 0 ){
				$this->recurring_reschedule = true;
			}
			do_action('em_event_save_events_pre', $this); //actions/filters only run if event is recurring
			//Make template event index, post, and meta (we change event dates, timestamps, rsvp dates and other recurrence-relative info whilst saving each event recurrence)
			$event = $this->to_array(true); //event template - for index
			if( !empty($event['event_attributes']) ) $event['event_attributes'] = serialize($event['event_attributes']);
			$post_fields = $wpdb->get_row('SELECT * FROM '.$wpdb->posts.' WHERE ID='.$this->post_id, ARRAY_A); //post to copy
			$post_fields['post_type'] = 'event'; //make sure we'll save events, not recurrence templates
			$meta_fields_map = $wpdb->get_results('SELECT meta_key,meta_value FROM '.$wpdb->postmeta.' WHERE post_id='.$this->post_id, ARRAY_A);
			$meta_fields = array();
			//convert meta_fields into a cleaner array
			foreach($meta_fields_map as $meta_data){
				$meta_fields[$meta_data['meta_key']] = $meta_data['meta_value'];
			}
			if( isset($meta_fields['_edit_last']) ) unset($meta_fields['_edit_last']);
			if( isset($meta_fields['_edit_lock']) ) unset($meta_fields['_edit_lock']);
			//remove id and we have a event template to feed to wpdb insert
			unset($event['event_id'], $event['post_id']); 
			unset($post_fields['ID']);
			unset($meta_fields['_event_id']);
			if( isset($meta_fields['_post_id']) ) unset($meta_fields['_post_id']); //legacy bugfix, post_id was never needed in meta table
			//remove recurrence meta info we won't need in events
			foreach( $this->recurrence_fields as $recurrence_field){
				$event[$recurrence_field] = null;
				if( isset($meta_fields['_'.$recurrence_field]) ) unset($meta_fields['_'.$recurrence_field]);
			}
			//Set the recurrence ID
			$event['recurrence_id'] = $meta_fields['_recurrence_id'] = $this->event_id;
			$event['recurrence'] = 0;
			
			//Let's start saving!
			$event_saves = $meta_inserts = array();
			$recurring_date_format = apply_filters('em_event_save_events_format', 'Y-m-d');
			$post_name = $this->sanitize_recurrence_slug( $post_fields['post_name'], $this->start()->format($recurring_date_format)); //template sanitized post slug since we'll be using this
			//First thing - times. If we're changing event times, we need to delete all events and recreate them with the right times, no other way
			
			if( $this->recurring_reschedule ){
				
				$this->delete_events(); //Delete old events beforehand, this will change soon
				$matching_days = $this->get_recurrence_days(); //Get days where events recur
				
				$event['event_date_created'] = current_time('mysql'); //since the recurrences are recreated
				unset($event['event_date_modified']);
				if( count($matching_days) > 0 ){
					//first save event post data
					$EM_DateTime = $this->start()->copy();
					foreach( $matching_days as $day ) {
						//set start date/time to $EM_DateTime for relative use further on
						$EM_DateTime->setTimestamp($day)->setTimeString($event['event_start_time']);
						$start_timestamp = $EM_DateTime->getTimestamp(); //for quick access later
						//rewrite post fields if needed
						//set post slug, which may need to be sanitized for length as we pre/postfix a date for uniqueness
						$event_slug_date = $EM_DateTime->format( $recurring_date_format );
						$event_slug = $this->sanitize_recurrence_slug($post_name, $event_slug_date);
						$event_slug = apply_filters('em_event_save_events_recurrence_slug', $event_slug.'-'.$event_slug_date, $event_slug, $event_slug_date, $day, $this); //use this instead
						$post_fields['post_name'] = $event['event_slug'] = apply_filters('em_event_save_events_slug', $event_slug, $post_fields, $day, $matching_days, $this); //deprecated filter
						//set start date
						$event['event_start_date'] = $meta_fields['_event_start_date'] = $EM_DateTime->getDate();
						$event['event_start'] = $meta_fields['_event_start'] = $EM_DateTime->getDateTime(true);
						//add rsvp date/time restrictions
						if( !empty($this->recurrence_rsvp_days) && is_numeric($this->recurrence_rsvp_days) ){
							if( $this->recurrence_rsvp_days > 0 ){
								$event_rsvp_end = $EM_DateTime->copy()->add(new DateInterval('P'.absint($this->recurrence_rsvp_days).'D'))->getDate(); //cloned so original object isn't modified
							}elseif($this->recurrence_rsvp_days < 0 ){
								$event_rsvp_end = $EM_DateTime->copy()->sub(new DateInterval('P'.absint($this->recurrence_rsvp_days).'D'))->getDate(); //cloned so original object isn't modified
							}else{
								$event_rsvp_end = $EM_DateTime->getDate();
							}
				 			$event['event_rsvp_end'] = $meta_fields['_event_rsvp_end'] = $event_rsvp_end;
						}else{
							$event['event_rsvp_end'] = $meta_fields['_event_rsvp_end'] = $event['event_start_date'];
						}
						

						//set end date
						$EM_DateTime->setTimeString($event['event_end_time']);
						if($this->recurrence_days > 0){
							//$EM_DateTime modified here, and used further down for UTC end date
							$event['event_end_date'] = $meta_fields['_event_end_date'] = $EM_DateTime->add(new DateInterval('P'.$this->recurrence_days.'D'))->getDate();
						}else{
							$event['event_end_date'] = $meta_fields['_event_end_date'] = $event['event_start_date'];
						}
						$end_timestamp = $EM_DateTime->getTimestamp(); //for quick access later
						$event['event_end'] = $meta_fields['_event_end'] = $EM_DateTime->getDateTime(true);
						//add extra date/time post meta
						$meta_fields['_event_start_local'] = $event['event_start_date'].' '.$event['event_start_time'];
						$meta_fields['_event_end_local'] = $event['event_end_date'].' '.$event['event_end_time'];
						
						//create the event
						if( $wpdb->insert($wpdb->posts, $post_fields ) ){
							$event['post_id'] = $post_id = $post_ids[$start_timestamp] = $wpdb->insert_id; //post id saved into event and also as a var for later user
							// Set GUID and event slug as per wp_insert_post
							$wpdb->update( $wpdb->posts, array( 'guid' => get_permalink( $post_id ) ), array('ID'=>$post_id) );
					 		//insert into events index table
							$event_saves[] = $wpdb->insert(EM_EVENTS_TABLE, $event);
							$event_ids[$post_id] = $event_id = $wpdb->insert_id;
							$event_dates[$event_id] = $start_timestamp;
					 		//create the meta inserts for each event
					 		$meta_fields['_event_id'] = $event_id;
					 		foreach($meta_fields as $meta_key => $meta_val){
					 			$meta_inserts[] = $wpdb->prepare("(%d, %s, %s)", array($post_id, $meta_key, $meta_val));
					 		}
						}else{
							$event_saves[] = false;
						}
						
				 	}
				 	//insert the metas in one go, faster than one by one
				 	if( count($meta_inserts) > 0 ){
					 	$result = $wpdb->query("INSERT INTO ".$wpdb->postmeta." (post_id,meta_key,meta_value) VALUES ".implode(',',$meta_inserts));
					 	if($result === false){
					 		$this->add_error(esc_html__('There was a problem adding custom fields to your recurring events.','events'));
					 	}
				 	}
				}else{
			 		$this->add_error(esc_html__('You have not defined a date range long enough to create a recurrence.','events'));
			 		$result = false;
			 	}
			}else{
				//we go through all event main data and meta data, we delete and recreate all meta data
				//now unset some vars we don't need to deal with since we're just updating data in the wp_em_events and posts table
				unset( $event['event_date_created'], $event['recurrence_id'], $event['recurrence'], $event['event_start_date'], $event['event_end_date'], $event['event_parent'] );
				$event['event_date_modified'] = current_time('mysql'); //since the recurrences are modified but not recreated
				unset( $post_fields['comment_count'], $post_fields['guid'], $post_fields['menu_order']);
				unset( $meta_fields['_event_parent'] ); // we'll ignore this and add it manually
				// clean the meta fields array to contain only the fields we actually need to overwrite i.e. delete and recreate, to avoid deleting unecessary individula recurrence data
				$exclude_meta_update_keys = apply_filters('em_event_save_events_exclude_update_meta_keys', array('_parent_id'), $this);
				//now we go through the recurrences and check whether things relative to dates need to be changed
				$events = EM_Events::get( array('recurrence'=>$this->event_id, 'scope'=>'all', 'status'=>'everything', 'array' => true ) );
			 	foreach($events as $event_array){ /* @var $EM_Event EM_Event */
			 		//set new start/end times to obtain accurate timestamp according to timezone and DST
			 		$EM_DateTime = $this->start()->copy()->modify($event_array['event_start_date']. ' ' . $event_array['event_start_time']);
			 		$start_timestamp = $EM_DateTime->getTimestamp();
			 		$event['event_start'] = $meta_fields['_event_start'] = $EM_DateTime->getDateTime(true);
			 		$end_timestamp = $EM_DateTime->modify($event_array['event_end_date']. ' ' . $event_array['event_end_time'])->getTimestamp();
			 		$event['event_end'] = $meta_fields['_event_end'] = $EM_DateTime->getDateTime(true);
			 		//set indexes for reference further down
			 		$event_ids[$event_array['post_id']] = $event_array['event_id'];
			 		$event_dates[$event_array['event_id']] = $start_timestamp;
			 		$post_ids[$start_timestamp] = $event_array['post_id'];
			 		//do we need to change the slugs?
				    //(re)set post slug, which may need to be sanitized for length as we pre/postfix a date for uniqueness
				    $EM_DateTime->setTimestamp($start_timestamp);
				    $event_slug_date = $EM_DateTime->format( $recurring_date_format );
				    $event_slug = $this->sanitize_recurrence_slug($post_name, $event_slug_date);
				    $event_slug = apply_filters('em_event_save_events_recurrence_slug', $event_slug.'-'.$event_slug_date, $event_slug, $event_slug_date, $start_timestamp, $this); //use this instead
				    $post_fields['post_name'] = $event['event_slug'] = apply_filters('em_event_save_events_slug', $event_slug, $post_fields, $start_timestamp, array(), $this); //deprecated filter
			 		//adjust certain meta information relative to dates and times
			 		if( !empty($this->recurrence_rsvp_days) && is_numeric($this->recurrence_rsvp_days) ){
			 			$event_rsvp_days = $this->recurrence_rsvp_days >= 0 ? '+'. $this->recurrence_rsvp_days: $this->recurrence_rsvp_days;
			 			$event_rsvp_end = $EM_DateTime->setTimestamp($start_timestamp)->modify($event_rsvp_days.' days')->getDate();
			 			$event['event_rsvp_end'] = $meta_fields['_event_rsvp_end'] = $event_rsvp_end;
			 		}else{
			 			$event['event_rsvp_end'] = $meta_fields['_event_rsvp_end'] = $event_array['event_start_date'];
			 		}
			 		$event['event_rsvp_time'] = $meta_fields['_event_rsvp_time'] = $event['event_rsvp_time'];
			 		//add meta fields we deleted and are specific to this event
			 		$meta_fields['_event_start_date'] = $event_array['event_start_date'];
			 		$meta_fields['_event_start_local'] = $event_array['event_start_date']. ' ' . $event_array['event_start_time'];
			 		$meta_fields['_event_end_date'] = $event_array['event_end_date'];
			 		$meta_fields['_event_end_local'] = $event_array['event_end_date']. ' ' . $event_array['event_end_time'];
					
			 		//overwrite event and post tables
			 		$wpdb->update(EM_EVENTS_TABLE, $event, array('event_id' => $event_array['event_id']));
			 		$wpdb->update($wpdb->posts, $post_fields, array('ID' => $event_array['post_id']));
			 		//save meta field data for insertion in one go
			 		foreach($meta_fields as $meta_key => $meta_val){
			 			$meta_inserts[] = $wpdb->prepare("(%d, %s, %s)", array($event_array['post_id'], $meta_key, $meta_val));
			 		}
			 	}
			 	// delete all meta we'll be updating
			 	if( !empty($post_ids) ){
			 		$sql = "DELETE FROM {$wpdb->postmeta} WHERE post_id IN (".implode(',', $post_ids).")";
			 		if( !empty($exclude_meta_update_keys) ){
			 			$sql .= " AND meta_key NOT IN (";
			 			$i = 0;
			 			foreach( $exclude_meta_update_keys as $k ){
			 				$sql.= ( $i > 0 ) ? ',%s' : '%s';
						    $i++;
					    }
					    $sql .= ")";
			 			$sql = $wpdb->prepare($sql, $exclude_meta_update_keys);
				    }
			 		$wpdb->query($sql);
			 	}
			 	// insert the metas in one go, faster than one by one
			 	if( count($meta_inserts) > 0 ){
				 	$result = $wpdb->query("INSERT INTO ".$wpdb->postmeta." (post_id,meta_key,meta_value) VALUES ".implode(',',$meta_inserts));
				 	if($result === false){
				 		$this->add_error(esc_html__('There was a problem adding custom fields to your recurring events.','events'));
				 	}
			 	}
			}
		 	//Next - Bookings. If we're completely rescheduling or just recreating bookings, we're deleting them and starting again
		 	if( ($this->recurring_reschedule || $this->recurring_recreate_bookings) && $this->recurring_recreate_bookings !== false ){ //if set specifically to false, we skip bookings entirely (ML translations for example)
			 	//first, delete all bookings & tickets if we haven't done so during the reschedule above - something we'll want to change later if possible so bookings can be modified without losing all data
			 	if( !$this->recurring_reschedule ){
				 	//create empty EM_Bookings and Tickets objects to circumvent extra loading of data and SQL queries
			 		$EM_Bookings = new EM_Bookings();
			 		$tickets = new \Contexis\Events\Tickets\Tickets();
			 		foreach($events as $event){ //$events was defined in the else statement above so we reuse it
			 			if($event['recurrence_id'] == $this->event_id){
			 				//trick EM_Bookings and Tickets to think it was loaded, and make use of optimized delete functions since 5.7.3.4
			 				$EM_Bookings->event_id = $tickets->event_id = $event['event_id'];
			 				$EM_Bookings->delete();
			 				$tickets->delete();
			 			}
			 		}
			 	}
			 	//if bookings hasn't been disabled, delete it all
			 	if( $this->event_rsvp ){
			 		$meta_inserts = array();
			 		foreach($this->get_bookings()->get_tickets() as $ticket){
			 			/* @var $ticket Ticket */
			 			//get array, modify event id and insert
			 			$ticket = $ticket->to_array();
			 			//empty cut-off dates of ticket, add them at per-event level
			 			unset($ticket['ticket_start']); unset($ticket['ticket_end']);
		 				if( !empty($ticket['ticket_meta']['recurrences']) ){
		 					$ticket_meta_recurrences = $ticket['ticket_meta']['recurrences'];
		 					unset($ticket['ticket_meta']['recurrences']);
		 				}
		 				//unset id
			 			unset($ticket['ticket_id']);
		 				//clean up ticket values
			 			foreach($ticket as $k => $v){
			 				if( empty($v) && $k != 'ticket_name' ){ 
			 					$ticket[$k] = 'NULL';
			 				}else{
			 					$data_type = !empty($ticket->fields[$k]['type']) ? $ticket->fields[$k]['type']:'%s';
			 					if(is_array($ticket[$k])) $v = serialize($ticket[$k]);
			 					$ticket[$k] = $wpdb->prepare($data_type,$v);
			 				}
			 			}
			 			//prep ticket meta for insertion with relative info for each event date
			 			$EM_DateTime = $this->start()->copy();
			 			foreach($event_ids as $event_id){
			 				$ticket['event_id'] = $event_id;
			 				$ticket['ticket_start'] = $ticket['ticket_end'] = 'NULL';
			 				//sort out cut-of dates
			 				if( !empty($ticket_meta_recurrences) ){
			 					$EM_DateTime->setTimestamp($event_dates[$event_id]); //by using EM_DateTime we'll generate timezone aware dates
			 					if( array_key_exists('start_days', $ticket_meta_recurrences) && $ticket_meta_recurrences['start_days'] !== false && $ticket_meta_recurrences['start_days'] !== null  ){
			 						$ticket_start_days = $ticket_meta_recurrences['start_days'] >= 0 ? '+'. $ticket_meta_recurrences['start_days']: $ticket_meta_recurrences['start_days'];
			 						$ticket_start_date = $EM_DateTime->modify($ticket_start_days.' days')->getDate();
			 						$ticket['ticket_start'] = "'". $ticket_start_date . ' '. $ticket_meta_recurrences['start_time'] ."'";
			 					}
			 					if( array_key_exists('end_days', $ticket_meta_recurrences) && $ticket_meta_recurrences['end_days'] !== false && $ticket_meta_recurrences['end_days'] !== null ){
			 						$ticket_end_days = $ticket_meta_recurrences['end_days'] >= 0 ? '+'. $ticket_meta_recurrences['end_days']: $ticket_meta_recurrences['end_days'];
			 						$EM_DateTime->setTimestamp($event_dates[$event_id]);
			 						$ticket_end_date = $EM_DateTime->modify($ticket_end_days.' days')->getDate();
			 						$ticket['ticket_end'] = "'". $ticket_end_date . ' '. $ticket_meta_recurrences['end_time'] . "'";
			 					}
			 				}
			 				//add insert data
			 				$meta_inserts[] = "(".implode(",",$ticket).")";
			 			}
			 		}
			 		$keys = "(".implode(",",array_keys($ticket)).")";
			 		$values = implode(',',$meta_inserts);
			 		$sql = "INSERT INTO ".EM_TICKETS_TABLE." $keys VALUES $values";
			 		$result = $wpdb->query($sql);
			 	}
		 	}elseif( $this->recurring_delete_bookings ){
		 		//create empty EM_Bookings and Tickets objects to circumvent extra loading of data and SQL queries
		 		$EM_Bookings = new EM_Bookings();
		 		$tickets = new \Contexis\Events\Tickets\Tickets();
		 		foreach($events as $event){ //$events was defined in the else statement above so we reuse it
		 			if($event['recurrence_id'] == $this->event_id){
		 				//trick EM_Bookings and Tickets to think it was loaded, and make use of optimized delete functions since 5.7.3.4
		 				$EM_Bookings->event_id = $tickets->event_id = $event['event_id'];
		 				$EM_Bookings->delete();
		 				$tickets->delete();
		 			}
		 		}
		 	}
		 	//copy the event tags and categories, which are automatically deleted/recreated by WP and EM_Categories
		 	foreach( self::get_taxonomies() as $tax_name => $tax_data ){
		 		//In MS Global mode, we also save category meta information for global lookups so we use our objects
				if( $tax_name == 'category' ){
					//we save index data for each category in in MS Global mode
					foreach($event_ids as $post_id => $event_id){
						//set and trick category event and post ids so it saves to the right place
						$this->get_categories()->event_id = $event_id;
						$this->get_categories()->post_id = $post_id;
						$this->get_categories()->save();
					}
				 	$this->get_categories()->event_id = $this->event_id;
				 	$this->get_categories()->post_id = $this->post_id;
				}else{
					//general taxonomies including event tags
				 	$terms = get_the_terms( $this->post_id, $tax_data['name']);
			 		$term_slugs = array();
			 		if( is_array($terms) ){
						foreach($terms as $term){
							if( !empty($term->slug) ) $term_slugs[] = $term->slug; //save of category will soft-fail if slug is empty
						}
			 		}
				 	foreach($post_ids as $post_id){
						wp_set_object_terms($post_id, $term_slugs, $tax_data['name']);
				 	}
				}
		 	}
			if( 'future' == $this->post_status ){
				$time = strtotime( $this->post_date_gmt . ' GMT' );
				foreach( $post_ids as $post_id ){
					if( !$this->recurring_reschedule ){
						wp_clear_scheduled_hook( 'publish_future_post', array( $post_id ) ); // clear anything else in the system
					}
					wp_schedule_single_event( $time, 'publish_future_post', array( $post_id ) );
				}
			}
			return apply_filters('em_event_save_events', !in_array(false, $event_saves) && $result !== false, $this, $event_ids, $post_ids);
		}
		return apply_filters('em_event_save_events', false, $this, $event_ids, $post_ids);
	}
	
	/**
	 * @param string $post_slug_postfix
	 * @return string
	 */
	public function sanitize_recurrence_slug( $post_name, $post_slug_postfix ){
		if( strlen($post_name.'-'.$post_slug_postfix) > 200 ){
			if( preg_match('/^(.+)(\-[0-9]+)$/', $post_name, $post_name_parts) ){
				$post_name_decoded = urldecode($post_name_parts[1]);
				$post_name_suffix =  $post_name_parts[2];
			}else{
				$post_name_decoded = urldecode($post_name);
				$post_name_suffix = '';
			}
			$post_name_maxlength = 200 - strlen( $post_name_suffix . '-' . $post_slug_postfix);
			if ( $post_name_parts[0] === $post_name_decoded.$post_name_suffix ){
				$post_name = substr( $post_name_decoded, 0, $post_name_maxlength );
			}else{
				$post_name = utf8_uri_encode( $post_name_decoded, $post_name_maxlength );
			}
			$post_name = rtrim( $post_name, '-' ). $post_name_suffix;
		}
		return apply_filters('em_event_sanitize_recurrence_slug', $post_name, $post_slug_postfix, $this);
	}
	
	/**
	 * Removes all recurrences of a recurring event.
	 * @return null
	 */
	function delete_events(){
		global $wpdb;
		do_action('em_event_delete_events_pre', $this);
		//So we don't do something we'll regret later, we could just supply the get directly into the delete, but this is safer
		$result = false;
		$events_array = array();
		if( $this->can_manage('delete_events', 'delete_others_events') ){
			//delete events from em_events table
			$sql = $wpdb->prepare('SELECT event_id FROM '.EM_EVENTS_TABLE.' WHERE (recurrence!=1 OR recurrence IS NULL)  AND recurrence_id=%d', $this->event_id);
			$event_ids = $wpdb->get_col( $sql );
			// go through each event and delete individually so individual hooks are fired appropriately
			foreach($event_ids as $event_id){
				$EM_Event = EM_Event::find_by_event_id( $event_id );
				if($EM_Event->recurrence_id == $this->event_id){
					$EM_Event->delete(true);
					$events_array[] = $EM_Event;
				}
			}
			$result = !empty($events_array) || (is_array($event_ids) && empty($event_ids)); // success if we deleted something, or if there was nothing to delete in the first place
		}
		$result = apply_filters('delete_events', $result, $this, $events_array); //Deprecated, use em_event_delete_events
		return apply_filters('em_event_delete_events', $result, $this, $events_array);
	}
	
	/**
	 * Returns the days that match the recurrance array passed (unix timestamps)
	 * @param array $recurrence
	 * @return array
	 */
	function get_recurrence_days(){
		//get timestampes for start and end dates, both at 12AM
		$start_date = $this->start()->copy()->setTime(0,0,0)->getTimestamp();
		$end_date = $this->end()->copy()->setTime(0,0,0)->getTimestamp();
			
		$weekdays = explode(",", $this->recurrence_byday); //what days of the week (or if monthly, one value at index 0)
		$matching_days = array(); //the days we'll be returning in timestamps
		
		//generate matching dates based on frequency type
		switch ( $this->recurrence_freq ){ /* @var EM_DateTime $current_date */
			case 'daily':
				//If daily, it's simple. Get start date, add interval timestamps to that and create matching day for each interval until end date.
				$current_date = $this->start()->copy()->setTime(0,0,0);
				while( $current_date->getTimestamp() <= $end_date ){
					$matching_days[] = $current_date->getTimestamp();
					$current_date->add(new DateInterval('P'. $this->recurrence_interval .'D'));
				}
				break;
			case 'weekly':
				//sort out week one, get starting days and then days that match time span of event (i.e. remove past events in week 1)
				$current_date = $this->start()->copy()->setTime(0,0,0);
				//then get the timestamps of weekdays during this first week, regardless if within event range
				$start_weekday_dates = array(); //Days in week 1 where there would events, regardless of event date range
				for($i = 0; $i < 7; $i++){
					
					if( in_array( $current_date->format('w'), $weekdays) ){
						$start_weekday_dates[] = $current_date->getTimestamp(); //it's in our starting week day, so add it
					}
					$current_date->add(new DateInterval('P1D')); //add a day
				}	
							
				//for each day of eventful days in week 1, add 7 days * weekly intervals
				foreach ($start_weekday_dates as $weekday_date){
					//Loop weeks by interval until we reach or surpass end date
					
					$current_date->setTimestamp($weekday_date);
					while($current_date->getTimestamp() <= $end_date){
						
						if( $current_date->getTimestamp() >= $start_date && $current_date->getTimestamp() <= $end_date ){
							if(count($matching_days) > 100) break; //limit to 1000 days (just in case
							$matching_days[] = $current_date->getTimestamp();
							
						}
						$current_date->add(new DateInterval('P'. ($this->recurrence_interval * 7 ) .'D'));
					}
				}//done!
				break;  
			case 'monthly':
				$current_date = $this->start()->copy();
				$current_date->modify($current_date->format('Y-m-01 00:00:00')); //Start date on first day of month, done this way to avoid 'first day of' issues in PHP < 5.6
				while( $current_date->getTimestamp() <= $this->end()->getTimestamp() ){
					$last_day_of_month = $current_date->format('t');
					$current_week_day = $current_date->format('w');
					$matching_month_days = array();
					for($day = 1; $day <= $last_day_of_month; $day++){
						if((int) $current_week_day == $this->recurrence_byday){
							$matching_month_days[] = $day;
						}
						$current_week_day = ($current_week_day < 6) ? $current_week_day+1 : 0;							
					}
					$matching_day = false;
					if( $this->recurrence_byweekno > 0 ){
						//date might not exist (e.g. fifth Sunday of a month) so only add if it exists
						if( !empty($matching_month_days[$this->recurrence_byweekno-1]) ){
							$matching_day = $matching_month_days[$this->recurrence_byweekno-1];
						}
					}else{
						$matching_day = array_pop($matching_month_days);
					}
					if( !empty($matching_day) ){
						$matching_date = $current_date->setDate( $current_date->format('Y'), $current_date->format('m'), $matching_day )->getTimestamp();
						if($matching_date >= $start_date && $matching_date <= $end_date){
							$matching_days[] = $matching_date;
						}
					}
					$current_date->modify($current_date->format('Y-m-01')); //done this way to avoid 'first day of ' PHP < 5.6 issues
					$current_date->add(new DateInterval('P'.$this->recurrence_interval.'M'));
				}
				break;
			case 'yearly':
				$EM_DateTime = $this->start()->copy();
				while( $EM_DateTime <= $this->end() ){
					$matching_days[] = $EM_DateTime->getTimestamp();
					$EM_DateTime->add(new DateInterval('P'.absint($this->recurrence_interval).'Y'));
				}			
				break;
		}
		sort($matching_days);
		
		
		return apply_filters('em_events_get_recurrence_days', $matching_days, $this);
	}
	
	function get_recurrence_description() {
		$EM_Event_Recurring = $this->get_event_recurrence(); 
		$recurrence = $this->to_array();
		$weekdays_name = array( translate('Sunday'),translate('Monday'),translate('Tuesday'),translate('Wednesday'),translate('Thursday'),translate('Friday'),translate('Saturday'));
		$monthweek_name = array('1' => __('the first %s of the month', 'events'),'2' => __('the second %s of the month', 'events'), '3' => __('the third %s of the month', 'events'), '4' => __('the fourth %s of the month', 'events'), '5' => __('the fifth %s of the month', 'events'), '-1' => __('the last %s of the month', 'events'));
		$output = sprintf (__('From %1$s to %2$s', 'events'),  $EM_Event_Recurring->event_start_date, $EM_Event_Recurring->event_end_date).", ";
		if ($EM_Event_Recurring->recurrence_freq == 'daily')  {
			$freq_desc =__('everyday', 'events');
			if ($EM_Event_Recurring->recurrence_interval > 1 ) {
				$freq_desc = sprintf (__("every %s days", 'events'), $EM_Event_Recurring->recurrence_interval);
			}
		}elseif ($EM_Event_Recurring->recurrence_freq == 'weekly')  {
			$weekday_array = explode(",", $EM_Event_Recurring->recurrence_byday);
			$natural_days = array();
			foreach($weekday_array as $day){
				array_push($natural_days, $weekdays_name[$day]);
			}
			$output .= implode(", ", $natural_days);
			$freq_desc = " " . __("every week", 'events');
			if ($EM_Event_Recurring->recurrence_interval > 1 ) {
				$freq_desc = " ".sprintf (__("every %s weeks", 'events'), $EM_Event_Recurring->recurrence_interval);
			}
			
		}elseif ($EM_Event_Recurring->recurrence_freq == 'monthly')  {
			$weekday_array = explode(",", $EM_Event_Recurring->recurrence_byday);
			$natural_days = array();
			foreach($weekday_array as $day){
				if( is_numeric($day) ){
					array_push($natural_days, $weekdays_name[$day]);
				}
			}
			$freq_desc = sprintf (($monthweek_name[$EM_Event_Recurring->recurrence_byweekno]), implode(" and ", $natural_days));
			if ($EM_Event_Recurring->recurrence_interval > 1 ) {
				$freq_desc .= ", ".sprintf (__("every %s months",'events'), $EM_Event_Recurring->recurrence_interval);
			}
		}elseif ($EM_Event_Recurring->recurrence_freq == 'yearly')  {
			$freq_desc = __("every year", 'events');
			if ($EM_Event_Recurring->recurrence_interval > 1 ) {
				$freq_desc .= sprintf (__("every %s years",'events'), $EM_Event_Recurring->recurrence_interval);
			}
		}else{
			$freq_desc = "[ERROR: corrupted database record]";
		}
		$output .= $freq_desc;
		return  $output;
	}	
	

	function to_array( bool $db = false ) : array {
		$event_array = parent::to_array($db);
		$event_array['event_start'] = $this->start()->valid ? $this->start(true)->format('Y-m-d H:i:s') : null;
		$event_array['event_end'] = $this->end()->valid ? $this->end(true)->format('Y-m-d H:i:s') : null;
		return apply_filters('em_event_to_array', $event_array, $this);
	}
	

	function can_manage( $owner_capability = false, $admin_capability = false, $user_to_check = false ){
		return apply_filters('em_event_can_manage', parent::can_manage($owner_capability, $admin_capability, $user_to_check), $this, $owner_capability, $admin_capability, $user_to_check);
	}
}

// FILTERS
// filters for general events field (corresponding to those of  "the _title")
add_filter('dbem_general', 'wptexturize');
add_filter('dbem_general', 'convert_chars');
add_filter('dbem_general', 'trim');
// filters for the notes field in html (corresponding to those of  "the _content")
add_filter('dbem_notes', 'wptexturize');
add_filter('dbem_notes', 'convert_smilies');
add_filter('dbem_notes', 'convert_chars');
add_filter('dbem_notes', 'wpautop');
add_filter('dbem_notes', 'prepend_attachment');
add_filter('dbem_notes', 'do_shortcode');
// filters for the notes field in html (corresponding to those of  "the _content")
add_filter('dbem_notes_excerpt', 'wptexturize');
add_filter('dbem_notes_excerpt', 'convert_smilies');
add_filter('dbem_notes_excerpt', 'convert_chars');
add_filter('dbem_notes_excerpt', 'wpautop');
add_filter('dbem_notes_excerpt', 'prepend_attachment');
add_filter('dbem_notes_excerpt', 'do_shortcode');
// RSS content filter
add_filter('dbem_notes_rss', 'convert_chars', 8);
add_filter('dbem_general_rss', 'esc_html', 8);
// Notes map filters
add_filter('dbem_notes_map', 'convert_chars', 8);
add_filter('dbem_notes_map', 'js_escape');
//embeds support if using placeholders
if ( is_object($GLOBALS['wp_embed']) ){
	add_filter( 'dbem_notes', array( $GLOBALS['wp_embed'], 'run_shortcode' ), 8 );
	add_filter( 'dbem_notes', array( $GLOBALS['wp_embed'], 'autoembed' ), 8 );
}

