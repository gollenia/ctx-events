<?php 
//define and clean up formats for display

use Contexis\Events\Collections\EventCollection;
use Contexis\Events\Views\EventView;

$summary_format = str_replace ( ">", "&gt;", str_replace ( "<", "&lt;", "#_EVENTNAME" ) );
$description_format = str_replace ( ">", "&gt;", str_replace ( "<", "&lt;", "#_EVENTEXCERPT" ) );
$location_format = str_replace ( ">", "&gt;", str_replace ( "<", "&lt;", "#_LOCATIONNAME, #_LOCATIONFULLLINE, #_LOCATIONCOUNTRY" ) );
$parsed_url = parse_url(get_bloginfo('url'));
$site_domain = preg_replace('/^www./', '', $parsed_url['host']);
$timezone_support = true;

//figure out limits
$ical_limit = 50;
$page_limit = $ical_limit > 50 || !$ical_limit ? 50:$ical_limit; //set a limit of 50 to output at a time, unless overall limit is lower
//get passed on $args and merge with defaults
$args = !empty($args) ? $args:array(); /* @var $args array */
$args = array_merge(array('limit'=>$page_limit, 'page'=>'1', 'owner'=>false, 'orderby'=>'event_start_date,event_start_time', 'scope' => 'future' ), $args);
$args = apply_filters('em_calendar_template_args',$args);
//get first round of events to show, we'll start adding more via the while loop
$events = EventCollection::find( $args );
$timezones = array();

//calendar header
$output_header = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//kids-team//events//EN";

//if timezone is supported, we output the blog timezone here
if( $timezone_support ){
	//get default blog timezone and output only if we're not in UTC or with manual offsets
	$blog_timezone = wp_timezone()->getName();
	if( !preg_match('/^UTC/', $blog_timezone) ){
		$output_header .= "
TZID:{$blog_timezone}
X-WR-TIMEZONE:{$blog_timezone}";
	}
}

echo preg_replace("/([^\r])\n/", "$1\r\n", $output_header);

//loop through events
$count = 0;
while ( count($events) > 0 ){
	foreach ( $events as $event ) {
		/* @var $event Event */
	    if( $ical_limit != 0 && $count > $ical_limit ) break; //we've reached our maximum
	    //figure out the timezone of this event, or if it's an offset and add to list of timezones and date ranges to define in VTIMEZONE
	    $show_timezone = $timezone_support && !preg_match('/^UTC/', $event->get_timezone()->getName());
	    if( $show_timezone ){
	    	$timezone = $event->start()->getTimezone()->getName();
	    	if( empty($timezones[$timezone]) ){
	    		$timezones[$timezone] = array( $event->start()->getTimestamp(), $event->end()->getTimestamp() );
	    	}else{
	    		if( $timezones[$timezone][0] > $event->start()->getTimestamp() ) $timezones[$timezone][0] = $event->start()->getTimestamp();
	    		if( $timezones[$timezone][1] < $event->end()->getTimestamp() ) $timezones[$timezone][1] = $event->end()->getTimestamp();
	    	}
	    }
	    //calculate the times along with timezone offsets
		if($event->event_all_day){
			//we get local time since we're representing a date not a time
			$dateStart	= ';VALUE=DATE:'.$event->start()->format('Ymd'); //all day
			$dateEnd	= ';VALUE=DATE:'.$event->end()->copy()->add(new DateInterval('P1D'))->format('Ymd'); //add one day
		}else{
			//get date output with timezone and local time if timezone output is enabled, or UTC time if not and/or if offset is manual
			if( $show_timezone ){
				//show local time and define a timezone
				$dateStart	= ':'.$event->start()->format('Ymd\THis');
				$dateEnd = ':'.$event->end()->format('Ymd\THis');
			}else{
				//create a UTC equivalent time for all events irrespective of timezone
				$dateStart	= ':'.$event->start(true)->format('Ymd\THis\Z');
				$dateEnd = ':'.$event->end(true)->format('Ymd\THis\Z');
			}
		}
		if( $show_timezone ){
			$dateStart = ';TZID='.$timezone . $dateStart;
			$dateEnd = ';TZID='.$timezone . $dateEnd;
		}
		if( !empty($event->event_date_modified) && $event->event_date_modified != '0000-00-00 00:00:00' ){
			$dateModified =  get_gmt_from_date($event->event_date_modified, 'Ymd\THis\Z');
		}else{
		    $dateModified = get_gmt_from_date($event->post_modified, 'Ymd\THis\Z');
		}
		
		//formats
		$summary = em_mb_ical_wordwrap('SUMMARY:' . EventView::render($event, $summary_format,'ical'));
		$description = em_mb_ical_wordwrap('DESCRIPTION:' . EventView::render($event, $description_format,'ical'));
		$url = 'URL:'.get_permalink($event->event_id);
		$url = wordwrap($url, 74, "\n ", true);
		$location = $geo = $apple_geo = $apple_location = $apple_location_title = $apple_structured_location = $categories = false;
		if( $event->location_id ){
			$location = em_mb_ical_wordwrap('LOCATION:' . EventView::render($event, $location_format, 'ical'));
			if( $event->get_location()->location_latitude || $event->get_location()->location_longitude ){
				$geo = 'GEO:'.$event->get_location()->location_latitude.";".$event->get_location()->location_longitude;
			}
			
			$apple_location = str_replace(';', '', html_entity_decode(str_replace('\;', ';', EventView::render($event, '#_LOCATIONFULLLINE, #_LOCATIONCOUNTRY', 'ical'))));
			$apple_location_title = str_replace('\;', '', html_entity_decode(str_replace('\;', ';', EventView::render($event, '#_LOCATIONNAME', 'ical'))));
			$apple_geo = !empty($geo) ? $event->get_location()->location_latitude.",".$event->get_location()->location_longitude:'0,0';
			$apple_structured_location = "X-APPLE-STRUCTURED-LOCATION;VALUE=URI;X-ADDRESS={$apple_location};X-APPLE-RADIUS=100;X-TITLE={$apple_location_title}:geo:{$apple_geo}";
			$apple_structured_location = str_replace('"', '\"', $apple_structured_location); //google chucks a wobbly with these on this line
			$apple_structured_location = em_mb_ical_wordwrap($apple_structured_location);
		}
		$categories = array();
		foreach( $event->get_categories() as $category ){ 
			$categories[] = $category->name;
		}
		$image = $event->get_image('full');
		
		//create a UID, make it unique and update independent
		$UID = $event->event_id . '@' . $site_domain;
		
		$UID = wordwrap("UID:".$UID, 74, "\r\n ", true);
		
//output ical item		
$output = "\r\n"."BEGIN:VEVENT
{$UID}
DTSTART{$dateStart}
DTEND{$dateEnd}
DTSTAMP:{$dateModified}
{$url}
{$summary}";
//Description if available
if( $description ){
    $output .= "\r\n" . $description;
}
//add featured image if exists
if( $image ){
	$image = wordwrap("ATTACH;FMTTYPE=image/jpeg:".esc_url_raw($image), 74, "\n ", true);
	$output .= "\r\n" . $image;
}
//add categories if there are any
if( !empty($categories) ){
	$categories = wordwrap("CATEGORIES:".implode(',', $categories), 74, "\n ", true);
	$output .= "\r\n" . $categories;
}
//Location if there is one
if( $location ){
	$output .= "\r\n" . $location;
	//geo coordinates if they exist
	if( $geo ){
		$output .= "\r\n" . $geo;
	}
	//create apple-compatible feature for locations
	if( !empty($apple_structured_location) ){
		$output .= "\r\n" . $apple_structured_location;
	}
}

//end the event
$output .= "
END:VEVENT";

		//clean up new lines, rinse and repeat
		echo preg_replace("/([^\r])\n/", "$1\r\n", $output);
		$count++;
	}
	if( $ical_limit != 0 && $count >= $ical_limit ){ 
	    //we've reached our limit, or showing one event only
	    break;
	}else{
	    //get next page of results
	    $args['page']++;
		$events = EventCollection::find( $args );
	}
}

//Now we sort out timezones and add it to the top of the output
if( $timezone_support && !empty($timezones) ){
	$vtimezones = array();
	foreach( $timezones as $timezone => $timezone_range ){
		$vtimezones[$timezone] = array();
		$previous_offset = false;
		//get the range of transitions, with a year's cushion so we can calculate the TZOFFSETFROM value
		$date_time_zone = wp_timezone();
		$timezone_transitions = $date_time_zone->getTransitions($timezone_range[0] - YEAR_IN_SECONDS, $timezone_range[1] + YEAR_IN_SECONDS);
		do{
			$current_transition = current($timezone_transitions);
			$transition_key = key($timezone_transitions);
			$next_transition = next($timezone_transitions);
			//format the offset to a UTC-OFFSET
			$current_offset_sign = $current_transition['offset'] < 0 ? '-' : '+';
			$current_offset_hours = absint(floor($current_transition['offset'] / HOUR_IN_SECONDS));
			$current_offset_minute_seconds = absint($current_transition['offset']) - $current_offset_hours*HOUR_IN_SECONDS;
			$current_offset_minutes = $current_offset_minute_seconds == 0 ? 0 : absint($current_offset_minute_seconds / MINUTE_IN_SECONDS);
			$current_transition['offset'] = $current_offset_sign . str_pad($current_offset_hours, 2, "0", STR_PAD_LEFT) . str_pad($current_offset_minutes, 2, "0", STR_PAD_LEFT);
			//skip transitions before and after the event date range, assuming we have some in between
			if( !empty($next_transition) && $next_transition['ts'] < $timezone_range[0] ){
				//remember previous offset
				$previous_offset = $current_transition['offset'];
				continue;
			}
			if( $current_transition['ts'] > $timezone_range[1] ) break;
			//modify the transition array directly and add it to vtimezones array
			unset( $current_transition['time'] );
			$current_transition['isdst'] = $current_transition['isdst'] ? 'DAYLIGHT':'STANDARD';
			$date_time = new DateTime($current_transition['ts']);
			$current_transition['ts'] = $date_time->format('Ymd\THis');
			$current_transition['offsetfrom'] = $previous_offset === false ? $current_transition['offset'] : $previous_offset;
			$vtimezones[$timezone][] = $current_transition;
			//remember previous offset
			$previous_offset = $current_transition['offset'];
		} while( $next_transition !== false );
	}
	foreach( $vtimezones as $timezone => $timezone_transitions ){
		$output = "
BEGIN:VTIMEZONE
TZID:{$timezone}
X-LIC-LOCATION:{$timezone}";
		foreach( $timezone_transitions as $transition ){
			$output .= "
BEGIN:{$transition['isdst']}
DTSTART:{$transition['ts']}
TZOFFSETFROM:{$transition['offsetfrom']}
TZOFFSETTO:{$transition['offset']}
TZNAME:{$transition['abbr']}
END:{$transition['isdst']}";
		}
		$output .= "
END:VTIMEZONE";
		echo preg_replace("/([^\r])\n/", "$1\r\n", $output);
	}
}

//calendar footer
echo "\r\n"."END:VCALENDAR";