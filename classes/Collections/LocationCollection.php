<?php

namespace Contexis\Events\Collections;


class LocationCollection extends \EM_Object {
	
	public static $num_rows;
	public static $num_rows_found;
	
	protected static $context = 'location';

	public static function is_enabled(){
		return true;
	}
	
	public static function get_post_search($args = array(), $filter = false, $request = array(), $accepted_args = array()){
		//supply $accepted_args to parent argument since we can't depend on late static binding until WP requires PHP 5.3 or later
		$accepted_args = !empty($accepted_args) ? $accepted_args : array_keys(self::get_default_search());
		$return = parent::get_post_search($args, $filter, $request, $accepted_args);
		//remove unwanted arguments or if not explicitly requested
		if( empty($_REQUEST['scope']) && empty($request['scope']) && !empty($return['scope']) ){
			unset($return['scope']);
		}
		return apply_filters('em_locations_get_post_search', $return);
	}
	
	/**
	 * Builds an array of SQL query conditions based on regularly used arguments
	 * @param array $args
	 * @return array
	 */
	
	
	/**
	 * Overrides EM_Object method to clean ambiguous fields and apply a filter to result.
	 * @see EM_Object::build_sql_orderby()
	 */
	 public static function build_sql_orderby( $args, $accepted_fields, $default_order = 'ASC' ){
		$orderby = parent::build_sql_orderby($args, $accepted_fields, get_option('dbem_events_default_order'));
		$orderby = self::build_sql_ambiguous_fields_helper($orderby); //fix ambiguous fields
		return apply_filters( 'em_locations_build_sql_orderby', $orderby, $args, $accepted_fields, $default_order );
	}
	
	/**
	 * Overrides EM_Object method to clean ambiguous fields and apply a filter to result.
	 * @see EM_Object::build_sql_groupby()
	 */
	public static function build_sql_groupby( $args, $accepted_fields, $groupby_order = false, $default_order = 'ASC' ){
		$groupby = parent::build_sql_groupby($args, $accepted_fields);
		//fix ambiguous fields and give them scope of events table
		$groupby = self::build_sql_ambiguous_fields_helper($groupby);
		return apply_filters( 'em_locations_build_sql_groupby', $groupby, $args, $accepted_fields );
	}
	
	/**
	 * Overrides EM_Object method to clean ambiguous fields and apply a filter to result.
	 * @see EM_Object::build_sql_groupby_orderby()
	 */
	 public static function build_sql_groupby_orderby($args, $accepted_fields, $default_order = 'ASC' ){
	    $group_orderby = parent::build_sql_groupby_orderby($args, $accepted_fields, get_option('dbem_events_default_order'));
		//fix ambiguous fields and give them scope of events table
		$group_orderby = self::build_sql_ambiguous_fields_helper($group_orderby);
		return apply_filters( 'em_locations_build_sql_groupby_orderby', $group_orderby, $args, $accepted_fields, $default_order );
	}
	
	/**
	 * Overrides EM_Object method to provide specific reserved fields and locations table.
	 * @see EM_Object::build_sql_ambiguous_fields_helper()
	 */
	protected static function build_sql_ambiguous_fields_helper( $fields, $reserved_fields = array(), $prefix = 'table_name' ){
		//This will likely be removed when PHP 5.3 is the minimum and LSB is a given
		return parent::build_sql_ambiguous_fields_helper($fields, array('post_id', 'location_id'), EM_LOCATIONS_TABLE);
	}
	
	/* 
	 * Generate a search arguments array from defalut and user-defined.
	 * @param array $array_or_defaults may be the array to override defaults
	 * @param array $array
	 * @return array
	 * @uses EM_Object#get_default_search()
	 */
	public static function get_default_search( $array_or_defaults = array(), $array = array() ){
		$defaults = array(
			'orderby' => 'location_name',
			'groupby' => false,
			'groupby_orderby' => 'location_name', //groups according to event start time, i.e. by default shows earliest event in a scope
			'groupby_order' => 'ASC', //groups according to event start time, i.e. by default shows earliest event in a scope
			'town' => false,
			'state' => false,
			'country' => false,
			'region' => false,
			'postcode' => false,
			'status' => 1, //approved locations only
			'scope' => 'all', //we probably want to search all locations by default, not like events
			'private' => current_user_can('read_private_locations'),
			'private_only' => false,
			'post_id' => false,
			//location-specific attributes
			'eventful' => false, //Locations that have an event (scope will also play a part here
			'eventless' => false, //Locations WITHOUT events, eventful takes precedence
		);
		//sort out whether defaults were supplied or just the array of search values
		if( empty($array) ){
			$array = $array_or_defaults;
		}else{
			$defaults = array_merge($defaults, $array_or_defaults);
		}

		$array['eventful'] = ( !empty($array['eventful']) && $array['eventful'] == true );
		$array['eventless'] = ( !empty($array['eventless']) && $array['eventless'] == true );
		if( is_admin() && !defined('DOING_AJAX') ){
			$defaults['owner'] = !current_user_can('read_others_locations') ? get_current_user_id():false;
		}
		return apply_filters('em_locations_get_default_search', parent::get_default_search($defaults, $array), $array, $defaults);
	}
}
?>