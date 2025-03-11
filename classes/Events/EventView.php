<?php
use \Contexis\Events\Models\Event;
class EventView {

	private Event $event;

	public static function render(Event $event, string $format, string $target="html") {
		$instance = new self();
		$instance->event = $event;
		return $instance->print($event, $format, $target);
	}

	private function print(Event $event, string $format, string $target="html") {	
		
	 	$event_string = $format;

		preg_match_all('/#@?__?\{[^}]+\}/', $format, $results);
		foreach($results[0] as $result) {
			if(substr($result, 0, 3 ) == "#@_"){
				$date = 'end';
				if( substr($result, 0, 4 ) == "#@__" ){
					$offset = 5;
					$show_site_timezone = true;
				}else{
					$offset = 4;
				}
			}else{
				$date = 'start';
				if( substr($result, 0, 3) == "#__" ){
					$offset = 4;
					$show_site_timezone = true;
				}else{
					$offset = 3;
				}
			}
			if( $date == 'end' && $this->event->event_start_date == $this->event->event_end_date ){
				$replace = apply_filters('em_event_output_placeholder', '', $this->event, $result, $target, array($result));
			}else{
				$date_format = substr( $result, $offset, (strlen($result)-($offset+1)) );
				if( !empty($show_site_timezone) ){
					$date_formatted = $this->event->$date()->copy()->setTimezone()->i18n($date_format);
				}else{
					$date_formatted = $this->event->$date()->i18n($date_format);
				}
				$replace = apply_filters('em_event_output_placeholder', $date_formatted, $this->event, $result, $target, array($result));
			}
			$event_string = str_replace($result,$replace,$event_string );
		}
		//This is for the custom attributes
		preg_match_all('/#_ATT\{([^}]+)\}(\{([^}]+\}?)\})?/', $event_string, $results);
		$attributes =  array('names'=>array(), 'values'=>array());
		foreach($results[0] as $resultKey => $result) {
			//check that we haven't mistakenly captured a closing bracket in second bracket set
			if( !empty($results[3][$resultKey]) && $results[3][$resultKey][0] == '/' ){
				$result = $results[0][$resultKey] = str_replace($results[2][$resultKey], '', $result);
				$results[3][$resultKey] = $results[2][$resultKey] = '';
			}
			//Strip string of placeholder and just leave the reference
			$attRef = substr( substr($result, 0, strpos($result, '}')), 6 );
			$attString = '';
			$placeholder_atts = array('#_ATT', $results[1][$resultKey]);
			if( is_array($this->event->event_attributes) && array_key_exists($attRef, $this->event->event_attributes) ){
				$attString = $this->event->event_attributes[$attRef];
			}elseif( !empty($results[3][$resultKey]) ){
				//Check to see if we have a second set of braces;
				$placeholder_atts[] = $results[3][$resultKey];
				$attStringArray = explode('|', $results[3][$resultKey]);
				$attString = $attStringArray[0];
			}elseif( !empty($attributes['values'][$attRef][0]) ){
			    $attString = $attributes['values'][$attRef][0];
			}
			$attString = apply_filters('em_event_output_placeholder', $attString, $this->event, $result, $target, $placeholder_atts);
			$event_string = str_replace($result, $attString ,$event_string );
		}
	 	
		preg_match_all('/\{([a-zA-Z0-9_\-,]+)\}(.+?)\{\/\1\}/s', $event_string, $conditionals);
		if( count($conditionals[0]) > 0 ){
			foreach($conditionals[1] as $key => $condition){

				$show_condition = match($condition) {
					'has_bookings' => $this->event->event_rsvp && get_option('dbem_rsvp_enabled'),
					'no_bookings' => (!$this->event->event_rsvp && get_option('dbem_rsvp_enabled')),
					'no_location' => !$this->event->has_location(),
					'has_location' => ( $this->event->has_location() && $this->event->get_location()->location_status ),
					'has_image' => $this->event->get_image_url() != '',
					'has_time' => ( $this->event->event_start_time != $this->event->event_end_time && !$this->event->event_all_day ),
					'all_day' => ($condition == 'all_day'),
					'has_spaces' => $this->event->event_rsvp && $this->event->get_bookings()->get_available_spaces() > 0,
					'fully_booked' => $this->event->event_rsvp && $this->event->get_bookings()->get_available_spaces() <= 0,
					'is_free' => !$this->event->event_rsvp || $this->event->is_free( $condition == 'is_free_now' ),
					'is_recurrence' => $this->event->is_recurrence(),
					'is_private' => $this->event->event_private == 1,
					default => false
				};

				$show_condition = apply_filters('em_event_output_show_condition', $show_condition, $condition, $conditionals[0][$key], $this->event);

				$replacement = '';
				
				if($show_condition){
					$placeholder_length = strlen($condition)+2;
					$replacement = substr($conditionals[0][$key], $placeholder_length, strlen($conditionals[0][$key])-($placeholder_length *2 +1));
				}

				$event_string = str_replace($conditionals[0][$key], apply_filters('em_event_output_condition', $replacement, $condition, $conditionals[0][$key], $this->event), $event_string);
			}
		}
	 	
		//Now let's check out the placeholders.
	 	preg_match_all("/(#@?_?[A-Za-z0-9_]+)({([^}]+)})?/", $event_string, $placeholders);
	 	$replaces = array();
		foreach($placeholders[1] as $key => $result) {
			$match = true;
			$replace = '';
			$full_result = $placeholders[0][$key];
			$placeholder_atts = array($result);
			if( !empty($placeholders[3][$key]) ) $placeholder_atts[] = $placeholders[3][$key];
			switch( $result ){
				//Event Details
				case '#_EVENTID':
					$replace = $this->event->event_id;
					break;
				case '#_EVENTPOSTID':
					$replace = $this->event->post_id;
					break;
				case '#_EVENTNAME':
					$replace = $this->event->event_name;
					break;
				case '#_EVENTNOTES':
					$replace = $this->event->post_content;
					break;				
				case '#_EVENTIMAGEURL':
					$replace =  esc_url($this->event->image_url);
					break;
				case '#_EVENTIMAGE':
	        		if($this->event->get_image_url() == '') break;

					$replace = "<img src='".esc_url($this->event->image_url)."' alt='".esc_attr($this->event->event_name)."'/>";

					if( empty($placeholders[3][$key])) break;

					$image_size = explode(',', $placeholders[3][$key]);
					
					if( is_array($image_size) && !(array_is_list($image_size) && count($image_size) > 1) ) break;

					
					$replace = get_the_post_thumbnail($this->event->post_id, $image_size, array('alt' => esc_attr($this->event->event_name)) );
					
					$image_attr = '';
					$image_args = array();
					if( empty($image_size[1]) && !empty($image_size[0]) ){    
						$image_attr = 'width="'.$image_size[0].'"';
						$image_args['w'] = $image_size[0];
					}elseif( empty($image_size[0]) && !empty($image_size[1]) ){
						$image_attr = 'height="'.$image_size[1].'"';
						$image_args['h'] = $image_size[1];
					}elseif( !empty($image_size[0]) && !empty($image_size[1]) ){
						$image_attr = 'width="'.$image_size[0].'" height="'.$image_size[1].'"';
						$image_args = array('w'=>$image_size[0], 'h'=>$image_size[1]);
					}
					$replace = "<img src='".esc_url(add_query_arg($image_args, $this->event->image_url))."' alt='".esc_attr($this->event->event_name)."' $image_attr />";
				
					break;
				//Times & Dates
				case '#_STARTTIME':
				case '#_ENDTIME':
					$replace = ($result == '#_24HSTARTTIME') ? $this->event->start()->format('H:i'):$this->event->end()->format('H:i');
					break;
				case '#_EVENTTIMES':
					//get format of time to show
					$replace = $this->event->output_times();
					break;
				case '#_EVENTPRICE':
					$replace = $this->event->get_formatted_price();
					break;
				case '#_EVENTDATES':
					//get format of time to show
					$replace = $this->event->output_dates();
					break;
				case '#_RECURRINGDATERANGE': //Outputs the #_EVENTDATES equivalent of the recurring event template pattern.
					$replace = $this->event->get_event_recurrence()->output_dates(); //if not a recurrence, we're running output_dates on $this->event
					break;
				case '#_RECURRINGPATTERN':
					$replace = '';
					if( $this->event->is_recurrence() || $this->event->is_recurring() ){
						$replace = $this->event->get_event_recurrence()->get_recurrence_description();
					}
					break;
				case '#_RECURRINGID':
					$replace = $this->event->recurrence_id;
					break;
				//Links
				case '#_EVENTPAGEURL': //deprecated	
				case '#_LINKEDNAME': //deprecated
				case '#_EVENTURL': //Just the URL
				case '#_EVENTLINK': //HTML Link
					$event_link = esc_url($this->event->get_permalink());
					if($result == '#_LINKEDNAME' || $result == '#_EVENTLINK'){
						$replace = '<a href="'.$event_link.'">'.esc_attr($this->event->event_name).'</a>';
					}else{
						$replace = $event_link;	
					}
					break;
				case '#_EDITEVENTURL':
				case '#_EDITEVENTLINK':
					if( $this->event->can_manage('edit_events','edit_others_events') ){
						$link = esc_url($this->event->get_edit_url());
						if( $result == '#_EDITEVENTLINK'){
							$replace = '<a href="'.$link.'">'.esc_html(sprintf(__('Edit Event','events'))).'</a>';
						}else{
							$replace = $link;
						}
					}	 
					break;
				case '#_AVAILABLESPACES':
					$replace = $this->event->event_rsvp && get_option('dbem_rsvp_enabled') ? $this->event->get_bookings()->get_available_spaces() : "0";
					break;
				case '#_BOOKEDSPACES':
					//This placeholder is actually a little misleading, as it'll consider reserved (i.e. pending) bookings as 'booked'
					if ($this->event->event_rsvp && get_option('dbem_rsvp_enabled')) {
						$replace = $this->event->get_bookings()->get_booked_spaces();
						if( get_option('dbem_bookings_approval_reserved') ){
							$replace += $this->event->get_bookings()->get_pending_spaces();
						}
					} else {
						$replace = "0";
					}
					break;
				case '#_PENDINGSPACES':
					$replace = $this->event->event_rsvp && get_option('dbem_rsvp_enabled') ? $this->event->get_bookings()->get_pending_spaces() : "0";
					break;
				case '#_SPACES':
					$replace = $this->event->get_bookings()->get_spaces();
					break;
				case '#_BOOKINGSURL':
				case '#_BOOKINGSLINK':
					if( $this->event->can_manage('manage_bookings','manage_others_bookings') ){
						$bookings_link = esc_url($this->event->get_bookings_url());
						if($result == '#_BOOKINGSLINK'){
							$replace = '<a href="'.$bookings_link.'" title="'.esc_attr($this->event->event_name).'">'.esc_html($this->event->event_name).'</a>';
						}else{
							$replace = $bookings_link;	
						}
					}
					break;
				case '#_BOOKINGSCUTOFF':
				case '#_BOOKINGSCUTOFFDATE':
				
				//Contact Person
				case '#_CONTACTNAME':
				case '#_CONTACTPERSON': //deprecated (your call, I think name is better)
					$replace = $this->event->get_contact()->display_name;
					break;
				case '#_CONTACTUSERNAME':
					$replace = $this->event->get_contact()->user_login;
					break;
				case '#_CONTACTEMAIL':
				case '#_CONTACTMAIL': //deprecated
					$replace = $this->event->get_contact()->user_email;
					break;
				case '#_CONTACTURL':
					$replace = $this->event->get_contact()->user_url;
					break;
				case '#_CONTACTID':
					$replace = $this->event->get_contact()->ID;
					break;
				case '#_CONTACTMETA':
					if( !empty($placeholders[3][$key]) ){
						$replace = get_user_meta($this->event->event_owner, $placeholders[3][$key], true);
					}
					break;
				case '#_ATTENDEES':
					ob_start();
					$template = em_locate_template('placeholders/attendees.php', true, array('Event'=>$this->event));
					
					$replace = ob_get_clean();
					break;
				case '#_ATTENDEESLIST':
					ob_start();
					$template = \em_locate_template('placeholders/attendeeslist.php', true, array('Event'=>$this->event));
					$replace = ob_get_clean();
					break;
				case '#_ATTENDEESPENDINGLIST':
					ob_start();
					$template = em_locate_template('placeholders/attendeespendinglist.php', true, array('Event'=>$this->event));
					$replace = ob_get_clean();
					break;
				//Ical Stuff
				case '#_EVENTICALURL':
				case '#_EVENTICALLINK':
					$replace = $this->get_ical_url();
					if( $result == '#_EVENTICALLINK' ){
						$replace = '<a href="'.esc_url($replace).'">iCal</a>';
					}
					break;
				case '#_EVENTWEBCALURL':
				case '#_EVENTWEBCALLINK':
					$replace = $this->get_ical_url();
					$replace = str_replace(array('http://','https://'), 'webcal://', $replace);
					if( $result == '#_EVENTWEBCALLINK' ){
						$replace = '<a href="'.esc_url($replace).'">webcal</a>';
					}
					break;
				//Event location (not physical location)
				
				default:
					$replace = $full_result;
					break;
			}
			$replaces[$full_result] = apply_filters('em_event_output_placeholder', $replace, $this->event, $full_result, $target, $placeholder_atts);
		}
		//sort out replacements so that during replacements shorter placeholders don't overwrite longer varieties.
		krsort($replaces);
		foreach($replaces as $full_result => $replacement){
			if( !in_array($full_result, array('#_NOTES','#_EVENTNOTES')) ){
				$event_string = str_replace($full_result, $replacement , $event_string );
			}else{
			    $new_placeholder = str_replace('#_', '__#', $full_result); //this will avoid repeated filters when locations/categories are parsed
			    $event_string = str_replace($full_result, $new_placeholder , $event_string );
				$desc_replace[$new_placeholder] = $replacement;
			}
		}
		//Time placeholders
		foreach($placeholders[1] as $result) {
			// matches all PHP START date and time placeholders
			if (preg_match('/^#[dDjlNSwzWFmMntLoYyaABgGhHisueIOPTZcrU]$/', $result)) {
				$replace = $this->event->start()->i18n(ltrim($result, "#"));
				$replace = apply_filters('em_event_output_placeholder', $replace, $this->event, $result, $target, array($result));
				$event_string = str_replace($result, $replace, $event_string );
			}
			// matches all PHP END time placeholders for endtime
			if (preg_match('/^#@[dDjlNSwzWFmMntLoYyaABgGhHisueIOPTZcrU]$/', $result)) {
				$replace = $this->event->end()->i18n(ltrim($result, "#@"));
				$replace = apply_filters('em_event_output_placeholder', $replace, $this->event, $result, $target, array($result));
				$event_string = str_replace($result, $replace, $event_string ); 
		 	}
		}
		//Now do dependent objects
		if( get_option('dbem_locations_enabled') ){
			if( !empty($this->event->location_id) && $this->event->get_location()->location_status ){
				$event_string = $this->event->get_location()->output($event_string, $target);
			}else{
				$EM_Location = new EM_Location();
				$event_string = LocationView::render($EM_Location, $event_string, $target);
			}
		}
		
	
		//for backwards compat and easy use, take over the individual category placeholders with the frirst cat in th elist.
		if( count($this->event->get_categories()) > 0 ){
			$EM_Category = $this->event->get_categories()->get_first();
		}
		if( empty($EM_Category) ) $EM_Category = new EM_Category();
		$event_string = $EM_Category->output($event_string, $target);
		
		
		
		$EM_Tags = new EM_Tags($this->event);
		if( count($EM_Tags) > 0 ){
			$EM_Tag = $EM_Tags->get_first();
		}
		if( empty($EM_Tag) ) $EM_Tag = new EM_Tag();
		$event_string = $EM_Tag->output($event_string, $target);
	
		
		//Finally, do the event notes, so that previous placeholders don't get replaced within the content, which may use shortcodes
		if( !empty($desc_replace) ){
			foreach($desc_replace as $full_result => $replacement){
				$event_string = str_replace($full_result, $replacement , $event_string );
			}
		}
		
		//do some specific formatting
		//TODO apply this sort of formatting to any output() function
		if( $target == 'ical' ){
		    //strip html and escape characters
		    $event_string = str_replace('\\','\\\\',strip_tags($event_string));
		    $event_string = str_replace(';','\;',$event_string);
		    $event_string = str_replace(',','\,',$event_string);
		    //remove and define line breaks in ical format
		    $event_string = str_replace('\\\\n','\n',$event_string);
		    $event_string = str_replace("\r\n",'\n',$event_string);
		    $event_string = str_replace("\n",'\n',$event_string);
		}
		return apply_filters('em_event_output', $event_string, $this->event, $format, $target);
	}

	private function get_ical_url(){
		global $wp_rewrite;
		if( !empty($wp_rewrite) && $wp_rewrite->using_permalinks() ){
			$return = trailingslashit($this->event->get_permalink()).'ical/';
		}else{
			$return = add_query_arg(['ical'=>1], $this->event->get_permalink());
		}
		return apply_filters('em_event_get_ical_url', $return);
	}

	
}