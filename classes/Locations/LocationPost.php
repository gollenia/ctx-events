<?php
class EM_Location_Post {

	const POST_TYPE = EM_POST_TYPE_LOCATION;
	public static function init(){ 
		//Front Side Modifiers
		if( !is_admin() ){
			
		}

		add_action( 'init', ['EM_Location_Post', "register_meta"] );
		add_action('init', array('EM_Location_Post', 'meta_query_filter'));
	}	
	
	/**
	 * Overrides the default post format of a location and can display a location as a page, which uses the page.php template.
	 * @param string $template
	 * @return string
	 */
	public static function single_template($template){
		global $post;
		if( !locate_template('single-'.EM_POST_TYPE_LOCATION.'.php') && $post->post_type == EM_POST_TYPE_LOCATION ){
			
			$post_templates = array('page.php','index.php');
			
			if( !empty($post_templates) ){
			    $post_template = locate_template($post_templates,false);
			    if( !empty($post_template) ) $template = $post_template;
			}
		}
		return $template;
	}

	

	public static function meta_query_filter() {
		add_filter(
			'rest_location_query',
			function ($args, $request) {
			  if ($meta_key = $request->get_param('metaKey')) {
				$args['meta_key'] = $meta_key;
				$args['meta_value'] = $request->get_param('metaValue');
			  }
			  return $args;
			},
			10,
			2
		  );
	}


	public static function register_meta() {

		
		$meta_array = [
			["_location_address", 'string', ''],
			["_location_town", 'string', ''],
			["_location_state", 'string', ''],
			["_location_postcode", 'string', ''],
			["_location_region", 'string', ''],
			["_location_url", 'string', ''],
			["_location_country", 'string', ''],
			["_location_latitude", "number", 0],
			["_location_longitude", "number", 0]
		];

		

		foreach($meta_array as $meta) {
			register_post_meta( 'location', $meta[0], [
				'type' => $meta[1],
				'single'       => true,
				'default' => $meta[2],
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback' => function() {
					return current_user_can( 'edit_posts' );
				},
				'show_in_rest' => [
					'schema' => [
						'default' => $meta[2],
						'style' => $meta[1]
					]
				]
			]);
		}
	}
}
EM_Location_Post::init();