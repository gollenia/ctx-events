<?php

namespace Contexis\Events\Models;
use Contexis\Events\Collections\EventCollection;
use Contexis\Events\Views\LocationView;
use WP_Query;
use WP_Post;


/**
 * Object that holds location info and related functions
 *
 * @property string $language       Language of the location, shorthand for location_language
 * @property string $translation    Whether or not a location is a translation (i.e. it was translated from an original location), shorthand for location_translation
 * @property int $parent            Location ID of parent location, shorthand for location_parent
 * @property int $id                The Location ID, case sensitive, shorthand for location_id
 * @property string $slug           Location slug, shorthand for location_slug
 * @property string $name            Location name, shorthand for location_name
 * @property int $owner              ID of author/owner, shorthand for location_owner
 * @property int $status             ID of post status, shorthand for location_status
 */
class Location extends \EM_Object {

	var $location_id = 0;
	var $post_id = 0;
	var $location_parent;
	var $location_private = 0;
	var $location_slug = '';
	var $location_name = '';
	var $location_address = '';
	var $location_town = '';
	var $location_state = '';
	var $location_postcode = '';
	var $location_region = '';
	var $location_country = '';
	var $location_latitude = 0;
	var $location_longitude = 0;
	var $post_content = '';
	var $location_owner = '';
	var $location_url = '';
	var $location_status = 0;
	var $location_language;
	var $location_translation = 0;
	/* anonymous submission information */
	var $owner_anonymous;
	var $owner_name;
	var $owner_email;
	//Other Vars
	public array $fields = array( 
		'location_id' => array('name'=>'id','type'=>'%d'),
		'post_id' => array('name'=>'post_id','type'=>'%d'),
		'location_parent' => array('type'=>'%d', 'null'=>true),
		'location_slug' => array('name'=>'slug','type'=>'%s', 'null'=>true), 
		'location_name' => array('name'=>'name','type'=>'%s', 'null'=>true), 
		'location_address' => array('name'=>'address','type'=>'%s','null'=>true),
		'location_town' => array('name'=>'town','type'=>'%s','null'=>true),
		'location_state' => array('name'=>'state','type'=>'%s','null'=>true),
		'location_postcode' => array('name'=>'postcode','type'=>'%s','null'=>true),
		'location_region' => array('name'=>'region','type'=>'%s','null'=>true),
		'location_country' => array('name'=>'country','type'=>'%s','null'=>true),
		'location_latitude' =>  array('name'=>'latitude','type'=>'%f','null'=>true),
		'location_longitude' => array('name'=>'longitude','type'=>'%f','null'=>true),
		'location_url' => array('name'=>'url','type'=>'%s','null'=>true),
		'post_content' => array('name'=>'description','type'=>'%s', 'null'=>true),
		'location_owner' => array('name'=>'owner','type'=>'%d', 'null'=>true),
		'location_status' => array('name'=>'status','type'=>'%d', 'null'=>true),
		'location_language' => array( 'type'=>'%s', 'null'=>true ),
		'location_translation' => array( 'type'=>'%d' ),
	);
	
	var $post_fields = array('post_id','location_slug','location_status', 'location_name','post_content','location_owner');
	var $location_attributes = array();
	public string $feedback_message = "";
	var array $errors = array();
	/**
	 * previous status of location
	 * @access protected
	 * @var mixed
	 */
	
	/* Post Variables - copied out of post object for easy IDE reference */
	var $post_status;
	var $post_type;
	

	static public function get_by_id($post_id) {
		$post = get_post($post_id);
		if (!$post) return new self();
		return self::find_by_post($post);
	}

	static public function find_by_location_id(int $location_id) {
		$args = array(
			'posts_per_page' => 1,
			'post_type'         => 'location',
			'meta_query' => array(
				array(
					'key' => '_location_id',
					'value' => $location_id,
				)
			)
		);
		$query = new WP_Query( $args );
		if (!$query->have_posts()) return new self();
		return self::find_by_post($query->post);
	}

	static function find_by_post(WP_Post $post) {
		$instance = new self();
		$instance->load_postdata($post);
		return $instance;
	}
	
	private function load_postdata(WP_Post $location_post){
		
			if( $location_post->post_status != 'auto-draft' ){
				
				$location_meta = get_post_meta($location_post->ID);
					
				//Get custom fields
				foreach($location_meta as $location_meta_key => $location_meta_val){
					$field_name = substr($location_meta_key, 1);
					if($location_meta_key[0] != '_'){
						$this->location_attributes[$location_meta_key] = ( is_array($location_meta_val) ) ? $location_meta_val[0]:$location_meta_val;
					}elseif( is_string($field_name) && !in_array($field_name, $this->post_fields) ){
						if( array_key_exists($field_name, $this->fields) ){
							$this->$field_name = $location_meta_val[0];
						}elseif( in_array($field_name, array('owner_name','owner_anonymous','owner_email')) ){
							$this->$field_name = $location_meta_val[0];
						}
					}
				}	
			}
			
			$this->post_id = $location_post->ID;
			$this->location_name = $location_post->post_title;
			$this->location_slug = $location_post->post_name;
			$this->location_owner = $location_post->post_author;
			$this->post_content = $location_post->post_content;
			$this->post_status = $location_post->post_status;
			$this->post_type = $location_post->post_type;
	}

	public function get_rest_fields() {
		return [ 
			'id' => $this->post_id, 
			'address' => $this->location_address,
			'zip' => $this->location_postcode,
			'city' => $this->location_town,
			'name' => $this->location_name,
			'url' => $this->location_url,
			'country' => $this->location_country,
			'state' => $this->location_state,
		];
	}
	
	/**
	 * Retrieve event information via POST (used in situations where posts aren't submitted via WP)
	 * @param boolean $validate whether or not to run validation, default is true
	 * @return boolean
	 */
	function get_post($validate = true){
		do_action('em_location_get_post_pre', $this);
		$this->location_name = ( !empty($_POST['location_name']) ) ? sanitize_post_field('post_title', $_POST['location_name'], $this->post_id, 'db'):'';
		$this->post_content = "";
		$this->get_post_meta(false);
		
		$result = $validate ? $this->validate():true; //validate both post and meta, otherwise return true
		//$this->compat_keys();
		return apply_filters('em_location_get_post', $result, $this);		
	}

	/**
	 * Since the post object has already been saved by the Gutenberg REST, we can safely get the post meta from the database.
	 * @param boolean $validate whether or not to run validation, default is true
	 * @return mixed
	 */
	function get_post_meta(){
		do_action('em_location_get_post_meta_pre', $this);
		
		$this->location_address = get_post_meta($this->post_id, '_location_address', true);
		$this->location_town = get_post_meta($this->post_id, '_location_town', true);
		$this->location_state = get_post_meta($this->post_id, '_location_state', true);
		$this->location_postcode = get_post_meta($this->post_id, '_location_postcode', true);
		$this->location_region = get_post_meta($this->post_id, '_location_region', true);
		$this->location_country = get_post_meta($this->post_id, '_location_country', true);
		$this->location_url = get_post_meta($this->post_id, '_location_url', true);
		$this->location_latitude = get_post_meta($this->post_id, '_location_latitude', true);
		$this->location_longitude = get_post_meta($this->post_id, '_location_longitude', true);
	
		//$this->compat_keys();
		return apply_filters('em_location_get_post_meta',true, $this, true); //if making a hook, assume that eventually $validate won't be passed on
	}
	
	function validate(){
		$validate_post = true;
		if( empty($this->location_name) ){
			$validate_post = false;
			$this->add_error( __('Location name','events').__(" is required.", 'events') );
		}

		return apply_filters('em_location_validate', $validate_post, $this );		
	}

	/**
	 * Checks if the location has be correctly stored previously
	 * @return boolean
	 */
	function location_exists() : bool {
		global $wpdb;
		if(empty($this->location_id)) return false;
		if( !empty($this->orphaned_location) && !empty($this->post_id) ) return true;
		return $wpdb->get_var('SELECT post_id FROM '.EM_LOCATIONS_TABLE." WHERE location_id={$this->location_id}") == $this->post_id;
	}
	
	function is_published(){
		return apply_filters('em_location_is_published', ($this->post_status == 'publish' || $this->post_status == 'private'), $this);
	}
	
	function has_events( $status = 1 ){	
		$events_count = EventCollection::find(array('location_id' => $this->location_id, 'status' => $status))->count();
		return apply_filters('em_location_has_events', $events_count > 0, $this);
	}
	
	
	function can_manage( $owner_capability = false, $admin_capability = false, $user_to_check = false ){
		$return = parent::can_manage($owner_capability, $admin_capability, $user_to_check);
		return apply_filters('em_location_can_manage', $return, $this, $owner_capability, $admin_capability, $user_to_check);
	}
	
	function get_permalink(){	

		$link = get_post_permalink($this->post_id);
		
		return apply_filters('em_location_get_permalink', $link, $this);	;
	}
	
	function get_ical_url(){
		global $wp_rewrite;
		if( !empty($wp_rewrite) && $wp_rewrite->using_permalinks() ){
			$return = trailingslashit($this->get_permalink()).'ical/';
		}else{
			$return = add_query_arg(['ical'=>1], $this->get_permalink());
		}
		return apply_filters('em_location_get_ical_url', $return);
	}
	
	function get_edit_url(){
		if( !$this->can_manage('edit_locations','edit_others_locations') ) return "";
		return apply_filters('em_location_get_edit_url', admin_url()."post.php?post={$this->post_id}&action=edit", $this);
	}
	
	function output($format, $target="html") {
		LocationView::render($this, $format, $target);
	}

	public function get_owner() {
		return $this->location_owner;
	}
	
}