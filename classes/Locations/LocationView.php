<?php

class LocationView {

	private EM_Location $location;

	public function __construct($location) {
		$this->location = $location;
	}

	public static function render($location, $format, $target="html") {
		$location_string = $format;
		$instance = new self($location);

		preg_match_all('/\{([a-zA-Z0-9_]+)\}(.+?)\{\/\1\}/s', $location_string, $conditionals);
		if( count($conditionals[0]) > 0 ){
			foreach($conditionals[1] as $key => $condition){
				$show_condition = false;
				if ($condition == 'has_events'){
					$show_condition = $location->has_events();
				}elseif ($condition == 'no_events'){
					$show_condition = $location->has_events() == false;
				}
				$show_condition = apply_filters('em_location_output_show_condition', $show_condition, $condition, $conditionals[0][$key], $location); 
				if($show_condition){
					$placeholder_length = strlen($condition)+2;
					$replacement = substr($conditionals[0][$key], $placeholder_length, strlen($conditionals[0][$key])-($placeholder_length *2 +1));
				}else{
					$replacement = '';
				}
				$location_string = str_replace($conditionals[0][$key], apply_filters('em_location_output_condition', $replacement, $condition, $conditionals[0][$key], $location), $location_string);
			}
		}

		preg_match_all('/#_LATT\{([^}]+)\}(\{([^}]+)\})?/', $location_string, $results);
		foreach($results[0] as $resultKey => $result) {
			if( !empty($results[3][$resultKey]) && $results[3][$resultKey][0] == '/' ){
				$result = $results[0][$resultKey] = str_replace($results[2][$resultKey], '', $result);
				$results[3][$resultKey] = $results[2][$resultKey] = '';
			}
			$attRef = substr( substr($result, 0, strpos($result, '}')), 7 );
			$attString = '';
			$placeholder_atts = array('#_ATT', $results[1][$resultKey]);
			if( is_array($location->location_attributes) && array_key_exists($attRef, $location->location_attributes) ){
				$attString = $location->location_attributes[$attRef];
			}elseif( !empty($results[3][$resultKey]) ){
				$placeholder_atts[] = $results[3][$resultKey];
				$attStringArray = explode('|', $results[3][$resultKey]);
				$attString = $attStringArray[0];
			}
			
			$location_string = str_replace($result, $attString ,$location_string );
		}
	 	preg_match_all("/(#@?_?[A-Za-z0-9_]+)({([^}]+)})?/", $location_string, $placeholders);
	 	$replaces = array();
		foreach($placeholders[1] as $key => $result) {
			$replace = '';
			$full_result = $placeholders[0][$key];
			$placeholder_atts = array($result);
			if( !empty($placeholders[3][$key]) ) $placeholder_atts[] = $placeholders[3][$key];
			switch( $result ){
				case '#_LOCATIONID':
					$replace = $location->post_id;
					break;
				case '#_LOCATIONPOSTID':
					$replace = $location->post_id;
					break;
				case '#_LOCATIONNAME':
					$replace = $location->location_name;
					break;
				case '#_LOCATIONADDRESS': 
					$replace = $location->location_address;
					break;
				case '#_LOCATIONTOWN':
					$replace = $location->location_town;
					break;
				case '#_LOCATIONSTATE':
					$replace = $location->location_state;
					break;
				case '#_LOCATIONPOSTCODE':
					$replace = $location->location_postcode;
					break;
				case '#_LOCATIONREGION':
					$replace = $location->location_region;
					break;
				case '#_LOCATIONCOUNTRY':
					$replace = $instance->get_country();
					break;
				case '#_LOCATIONFULLLINE':
				case '#_LOCATIONFULLBR':
					$glue = $result == '#_LOCATIONFULLLINE' ? ', ':'<br />';
					$replace = $instance->get_full_address($glue);
					break;
				case '#_LOCATIONLONGITUDE':
					$replace = $location->location_longitude;
					break;
				case '#_LOCATIONLATITUDE':
					$replace = $location->location_latitude;
					break;
				case '#_LOCATIONNOTES':
					$replace = $location->post_content;
					break;
				case '#_LOCATIONPASTEVENTS':
	
				default:
					$replace = $full_result;
					break;
			}
			$replaces[$full_result] = apply_filters('em_location_output_placeholder', $replace, $location, $full_result, $target, $placeholder_atts);
		}
		//sort out replacements so that during replacements shorter placeholders don't overwrite longer varieties.
		krsort($replaces);
		foreach($replaces as $full_result => $replacement){
			if( !in_array($full_result, array('#_DESCRIPTION','#_LOCATIONNOTES')) ){
				$location_string = str_replace($full_result, $replacement , $location_string );
			}else{
				$desc_replace[$full_result] = $replacement;
			}
		}

		//Finally, do the location notes, so that previous placeholders don't get replaced within the content, which may use shortcodes
		if( !empty($desc_replace) ){
			foreach($desc_replace as $full_result => $replacement){
				$location_string = str_replace($full_result, $replacement , $location_string );
			}
		}

		return apply_filters('em_location_output', $location_string, $location, $format, $target);	
	}

	function get_full_address($glue = ', ', $include_country = false){
		$location_array = array();
		if( !empty($this->location->location_address) ) $location_array[] = $this->location->location_address;
		if( !empty($this->location->location_town) ) $location_array[] = $this->location->location_town;
		if( !empty($this->location->location_state) ) $location_array[] = $this->location->location_state;
		if( !empty($this->llocation->ocation_postcode) ) $location_array[] = $this->location->location_postcode;
		if( !empty($this->location->location_region) ) $location_array[] = $this->location->location_region;
		if( $include_country ) $location_array[] = $this->get_country();
		return implode($glue, $location_array);
	}

	function get_country(){
		$countries = \Contexis\Events\Intl\Countries::get();
		if( !empty($countries[$this->location->location_country]) ){
			return apply_filters('em_location_get_country', $countries[$this->location->location_country], $this);
		}
		return apply_filters('em_location_get_country', false, $this);
			
	}
}