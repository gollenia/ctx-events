<?php

use Contexis\Events\Collections\EventCollection;
use Contexis\Events\Models\Event;

class EventRecurring extends Event {

	var $recurrence_id;
	var $recurrence = 0;
	var $recurrence_interval;
	var $recurrence_freq;
	var $recurrence_byday;
	var $recurrence_days = 0;
	var $recurrence_byweekno;
	var $recurrence_rsvp_days;
	var $recurrence_fields = array('recurrence', 'recurrence_interval', 'recurrence_freq', 'recurrence_days', 'recurrence_byday', 'recurrence_byweekno', 'recurrence_rsvp_days');

	var $recurring_reschedule = false;
	/**
	 * If set to true, recurring events will delete bookings and tickets of recurrences and recreate tickets. If set explicitly to false bookings will be ignored when creating recurrences.
	 * @var boolean
	 */
	var $recurring_recreate_bookings;
	/**
	 * Flag used for when saving a recurring event that previously had bookings enabled and then subsequently disabled.
	 * If set to true, and $this->recurring_recreate_bookings is false, bookings and tickets of recurrences will be deleted.
	 * @var boolean
	 */
	var $recurring_delete_bookings = false;
	/**
	 * If the event was just added/created during this execution, value will be true. Useful when running validation or making decisions on taking actions when events are saved/created for the first time.
	 * @var boolean
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
	 * Gets the event recurrence template, which is an Event object (based off an event-recurring post)
	 * @return Event
	 */
	function get_event_recurrence(){
		if(!$this->is_recurring()){
			return Event::find_by_event_id($this->recurrence_id);
		}else{
			return $this;
		}
	}

	function save_meta()
	{
		parent::save_meta();
		if( $this->is_recurring() ){
			//save recurrence meta
			foreach($this->recurrence_fields as $recurrence_field){
				if( isset($_REQUEST[$recurrence_field]) ){
					$this->$recurrence_field = $_REQUEST[$recurrence_field];
					update_post_meta($this->post_id, '_'.$recurrence_field, $this->$recurrence_field);
				}
			}
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
					$date_time = $this->start()->copy();
					foreach( $matching_days as $day ) {
						$date_time->setTimestamp($day)->setTimeString($event['event_start_time']);
						$start_timestamp = $date_time->getTimestamp(); //for quick access later
						//rewrite post fields if needed
						//set post slug, which may need to be sanitized for length as we pre/postfix a date for uniqueness
						$event_slug_date = $date_time->format( $recurring_date_format );
						$event_slug = $this->sanitize_recurrence_slug($post_name, $event_slug_date);
						$event_slug = apply_filters('em_event_save_events_recurrence_slug', $event_slug.'-'.$event_slug_date, $event_slug, $event_slug_date, $day, $this); //use this instead
						$post_fields['post_name'] = $event['event_slug'] = apply_filters('em_event_save_events_slug', $event_slug, $post_fields, $day, $matching_days, $this); //deprecated filter
						//set start date
						$event['event_start_date'] = $meta_fields['_event_start_date'] = $date_time->format('Y-m-d');
						$event['event_start'] = $meta_fields['_event_start'] = $date_time->format('Y-m-d H:i:s');
						//add rsvp date/time restrictions
						if( !empty($this->recurrence_rsvp_days) && is_numeric($this->recurrence_rsvp_days) ){
							if( $this->recurrence_rsvp_days > 0 ){
								$event_rsvp_end = $date_time->copy()->add(new DateInterval('P'.absint($this->recurrence_rsvp_days).'D'))->format('Y-m-d'); //cloned so original object isn't modified
							}elseif($this->recurrence_rsvp_days < 0 ){
								$event_rsvp_end = $date_time->copy()->sub(new DateInterval('P'.absint($this->recurrence_rsvp_days).'D'))->format('Y-m-d'); //cloned so original object isn't modified
							}else{
								$event_rsvp_end = $date_time->format('Y-m-d');
							}
				 			$event['event_rsvp_end'] = $meta_fields['_event_rsvp_end'] = $event_rsvp_end;
						}else{
							$event['event_rsvp_end'] = $meta_fields['_event_rsvp_end'] = $event['event_start_date'];
						}
						

						//set end date
						$date_time->setTimeString($event['event_end_time']);
						if($this->recurrence_days > 0){
							$event['event_end_date'] = $meta_fields['_event_end_date'] = $date_time->add(new DateInterval('P'.$this->recurrence_days.'D'))->format('Y-m-d');
						}else{
							$event['event_end_date'] = $meta_fields['_event_end_date'] = $event['event_start_date'];
						}
						$end_timestamp = $date_time->getTimestamp(); //for quick access later
						$event['event_end'] = $meta_fields['_event_end'] = $date_time->format('Y-m-d H:i:s');
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
				unset( $event['event_date_created'], $event['recurrence_id'], $event['recurrence'], $event['event_start_date'], $event['event_end_date']);
				$event['event_date_modified'] = current_time('mysql'); //since the recurrences are modified but not recreated
				unset( $post_fields['comment_count'], $post_fields['guid'], $post_fields['menu_order']);
				// clean the meta fields array to contain only the fields we actually need to overwrite i.e. delete and recreate, to avoid deleting unecessary individula recurrence data
				$exclude_meta_update_keys = apply_filters('em_event_save_events_exclude_update_meta_keys', array('_parent_id'), $this);
				//now we go through the recurrences and check whether things relative to dates need to be changed
				$events = EventCollection::find( array('recurrence'=>$this->event_id, 'scope'=>'all', 'status'=>'everything', 'array' => true ) );
			 	foreach($events as $event_array){ /* @var $event Event */
			 		//set new start/end times to obtain accurate timestamp according to timezone and DST
			 		$date_time = $this->start()->copy()->modify($event_array['event_start_date']. ' ' . $event_array['event_start_time']);
			 		$start_timestamp = $date_time->getTimestamp();
			 		$event['event_start'] = $meta_fields['_event_start'] = $date_time->format('Y-m-d H:i:s');
			 		$end_timestamp = $date_time->modify($event_array['event_end_date']. ' ' . $event_array['event_end_time'])->getTimestamp();
			 		$event['event_end'] = $meta_fields['_event_end'] = $date_time->format('Y-m-d H:i:s');
			 		//set indexes for reference further down
			 		$event_ids[$event_array['post_id']] = $event_array['event_id'];
			 		$event_dates[$event_array['event_id']] = $start_timestamp;
			 		$post_ids[$start_timestamp] = $event_array['post_id'];
			 		//do we need to change the slugs?
				    //(re)set post slug, which may need to be sanitized for length as we pre/postfix a date for uniqueness
				    $date_time->setTimestamp($start_timestamp);
				    $event_slug_date = $date_time->format( $recurring_date_format );
				    $event_slug = $this->sanitize_recurrence_slug($post_name, $event_slug_date);
				    $event_slug = apply_filters('em_event_save_events_recurrence_slug', $event_slug.'-'.$event_slug_date, $event_slug, $event_slug_date, $start_timestamp, $this); //use this instead
				    $post_fields['post_name'] = $event['event_slug'] = apply_filters('em_event_save_events_slug', $event_slug, $post_fields, $start_timestamp, array(), $this); //deprecated filter
			 		//adjust certain meta information relative to dates and times
			 		if( !empty($this->recurrence_rsvp_days) && is_numeric($this->recurrence_rsvp_days) ){
			 			$event_rsvp_days = $this->recurrence_rsvp_days >= 0 ? '+'. $this->recurrence_rsvp_days: $this->recurrence_rsvp_days;
			 			$event_rsvp_end = $date_time->setTimestamp($start_timestamp)->modify($event_rsvp_days.' days')->format('Y-m-d');
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
			 		$EM_Bookings = new EM_Bookings;
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
			 		foreach($this->get_tickets() as $ticket){
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
			 			$date_time = $this->start()->copy();
			 			foreach($event_ids as $event_id){
			 				$ticket['event_id'] = $event_id;
			 				$ticket['ticket_start'] = $ticket['ticket_end'] = 'NULL';
			 				//sort out cut-of dates
			 				if( !empty($ticket_meta_recurrences) ){
			 					$date_time->setTimestamp($event_dates[$event_id]); 
			 					if( array_key_exists('start_days', $ticket_meta_recurrences) && $ticket_meta_recurrences['start_days'] !== false && $ticket_meta_recurrences['start_days'] !== null  ){
			 						$ticket_start_days = $ticket_meta_recurrences['start_days'] >= 0 ? '+'. $ticket_meta_recurrences['start_days']: $ticket_meta_recurrences['start_days'];
			 						$ticket_start_date = $date_time->modify($ticket_start_days.' days')->format('Y-m-d');
			 						$ticket['ticket_start'] = "'". $ticket_start_date . ' '. $ticket_meta_recurrences['start_time'] ."'";
			 					}
			 					if( array_key_exists('end_days', $ticket_meta_recurrences) && $ticket_meta_recurrences['end_days'] !== false && $ticket_meta_recurrences['end_days'] !== null ){
			 						$ticket_end_days = $ticket_meta_recurrences['end_days'] >= 0 ? '+'. $ticket_meta_recurrences['end_days']: $ticket_meta_recurrences['end_days'];
			 						$date_time->setTimestamp($event_dates[$event_id]);
			 						$ticket_end_date = $date_time->modify($ticket_end_days.' days')->format('Y-m-d');
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
		 		$EM_Bookings = new EM_Bookings;
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
	 * Ensures a post slug is the correct length when the date postfix is added, which takes into account multibyte and url-encoded characters and WP unique suffixes.
	 * If a url-encoded slug is nearing 200 characters (the data character limit in the db table), adding a date to the end will cause issues when saving to the db.
	 * This function checks if the final slug is longer than 200 characters and removes one entire character rather than part of a hex-encoded character, until the right size is met.
	 * @param string $post_name
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
				$event = Event::find_by_event_id( $event_id );
				if($event->recurrence_id == $this->event_id){
					$event->delete(true);
					$events_array[] = $event;
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
		switch ( $this->recurrence_freq ){ 
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
				//loop months starting this month by intervals
				$current_date = $this->start()->copy();
				$current_date->modify($current_date->format('Y-m-01 00:00:00')); //Start date on first day of month, done this way to avoid 'first day of' issues in PHP < 5.6
				while( $current_date->getTimestamp() <= $this->end()->getTimestamp() ){
					$last_day_of_month = $current_date->format('t');
					//Now find which day we're talking about
					$current_week_day = $current_date->format('w');
					$matching_month_days = array();
					//Loop through days of this years month and save matching days to temp array
					for($day = 1; $day <= $last_day_of_month; $day++){
						if((int) $current_week_day == $this->recurrence_byday){
							$matching_month_days[] = $day;
						}
						$current_week_day = ($current_week_day < 6) ? $current_week_day+1 : 0;							
					}
					//Now grab from the array the x day of the month
					$matching_day = false;
					if( $this->recurrence_byweekno > 0 ){
						//date might not exist (e.g. fifth Sunday of a month) so only add if it exists
						if( !empty($matching_month_days[$this->recurrence_byweekno-1]) ){
							$matching_day = $matching_month_days[$this->recurrence_byweekno-1];
						}
					}else{
						//last day of month, so we pop the last matching day
						$matching_day = array_pop($matching_month_days);
					}
					//if we have a matching day, get the timestamp, make sure it's within our start/end dates for the event, and add to array if it is
					if( !empty($matching_day) ){
						$matching_date = $current_date->setDate( $current_date->format('Y'), $current_date->format('m'), $matching_day )->getTimestamp();
						if($matching_date >= $start_date && $matching_date <= $end_date){
							$matching_days[] = $matching_date;
						}
					}
					//add the monthly interval to the current date, but set to 1st of current month first so we don't jump months where $current_date is 31st and next month there's no 31st (so a month is skipped)
					$current_date->modify($current_date->format('Y-m-01')); //done this way to avoid 'first day of ' PHP < 5.6 issues
					$current_date->add(new DateInterval('P'.$this->recurrence_interval.'M'));
				}
				break;
			case 'yearly':
				$date_time = $this->start()->copy();
				while( $date_time <= $this->end() ){
					$matching_days[] = $date_time->getTimestamp();
					$date_time->add(new DateInterval('P'.absint($this->recurrence_interval).'Y'));
				}			
				break;
		}
		sort($matching_days);
		
		
		return apply_filters('em_events_get_recurrence_days', $matching_days, $this);
	}
	
	/**
	 * If event is recurring, set recurrences to same status as template
	 * @param $status
	 */ /*
	function set_status_events($status){
		//give sub events same status
		global $wpdb;
		//get post and event ids of recurrences
		$post_ids = $event_ids = array();
		$events_array = EventCollection::find( array('recurrence'=>$this->event_id, 'scope'=>'all', 'status'=>false, 'array'=>true) ); //only get recurrences that aren't trashed or drafted
		foreach( $events_array as $event_array ){
			$post_ids[] = absint($event_array['post_id']);
			$event_ids[] = absint($event_array['event_id']);
		}
		if( !empty($post_ids) ){
			//decide on what status to set and update wp_posts in the process
			if($status === null){ 
				$set_status='NULL'; //draft post
				$post_status = 'draft'; //set post status in this instance
			}elseif( $status == -1 ){ //trashed post
				$set_status = -1;
				$post_status = 'trash'; //set post status in this instance
			}else{
				$set_status = $status ? 1:0; //published or pending post
				$post_status = $set_status ? 'publish':'pending';
			}

			$result = $wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->posts." SET post_status=%s WHERE ID IN (". implode(',', $post_ids) .')', array($post_status)) );
			restore_current_blog();
			$result = $result && $wpdb->query( $wpdb->prepare("UPDATE ".EM_EVENTS_TABLE." SET event_status=%s WHERE event_id IN (". implode(',', $event_ids) .')', array($set_status)) );
		}
		return apply_filters('em_event_set_status_events', $result !== false, $status, $this, $event_ids, $post_ids);
	} */
	
	/**
	 * Returns a string representation of this recurrence. Will return false if not a recurrence
	 * @return string
	 */
	function get_recurrence_description() {
		$event_recurring = $this->get_event_recurrence(); 
		$recurrence = $this->to_array();
		$weekdays_name = array( translate('Sunday'),translate('Monday'),translate('Tuesday'),translate('Wednesday'),translate('Thursday'),translate('Friday'),translate('Saturday'));
		$monthweek_name = array('1' => __('the first %s of the month', 'events'),'2' => __('the second %s of the month', 'events'), '3' => __('the third %s of the month', 'events'), '4' => __('the fourth %s of the month', 'events'), '5' => __('the fifth %s of the month', 'events'), '-1' => __('the last %s of the month', 'events'));
		$output = sprintf (__('From %1$s to %2$s', 'events'),  $event_recurring->event_start_date, $event_recurring->event_end_date).", ";
		if ($event_recurring->recurrence_freq == 'daily')  {
			$freq_desc =__('everyday', 'events');
			if ($event_recurring->recurrence_interval > 1 ) {
				$freq_desc = sprintf (__("every %s days", 'events'), $event_recurring->recurrence_interval);
			}
		}elseif ($event_recurring->recurrence_freq == 'weekly')  {
			$weekday_array = explode(",", $event_recurring->recurrence_byday);
			$natural_days = array();
			foreach($weekday_array as $day){
				array_push($natural_days, $weekdays_name[$day]);
			}
			$output .= implode(", ", $natural_days);
			$freq_desc = " " . __("every week", 'events');
			if ($event_recurring->recurrence_interval > 1 ) {
				$freq_desc = " ".sprintf (__("every %s weeks", 'events'), $event_recurring->recurrence_interval);
			}
			
		}elseif ($event_recurring->recurrence_freq == 'monthly')  {
			$weekday_array = explode(",", $event_recurring->recurrence_byday);
			$natural_days = array();
			foreach($weekday_array as $day){
				if( is_numeric($day) ){
					array_push($natural_days, $weekdays_name[$day]);
				}
			}
			$freq_desc = sprintf (($monthweek_name[$event_recurring->recurrence_byweekno]), implode(" and ", $natural_days));
			if ($event_recurring->recurrence_interval > 1 ) {
				$freq_desc .= ", ".sprintf (__("every %s months",'events'), $event_recurring->recurrence_interval);
			}
		}elseif ($event_recurring->recurrence_freq == 'yearly')  {
			$freq_desc = __("every year", 'events');
			if ($event_recurring->recurrence_interval > 1 ) {
				$freq_desc .= sprintf (__("every %s years",'events'), $event_recurring->recurrence_interval);
			}
		}else{
			$freq_desc = "[ERROR: corrupted database record]";
		}
		$output .= $freq_desc;
		return  $output;
	}
}