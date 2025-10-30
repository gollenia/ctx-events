<?php

namespace Contexis\Events\Admin;
use WP_Query;

class SpeakerAdmin {

    public static function register() {
        $instance = new self;
        
        add_filter( 'manage_event-speaker_posts_columns', array($instance, 'set_custom_columns') );
        add_action( 'manage_event-speaker_posts_custom_column' , array($instance, 'custom_column'), 10, 2 );
        add_action( 'edit_form_advanced', [$instance, 'add_back_button'] );
    }

    
    public function set_custom_columns($columns) {
        $columns['email'] = __( 'E-Mail', 'ctx-theme' );

        return $columns;
    }

    public function custom_column( $column, $post_id ) {
        if($column == "email") {
            $email = get_post_meta( $post_id , 'email' , true );
            
            echo $email;
        }
    }

	public static function get($id) {
		if($id == 0) return false;
		$args = array(
			'p'         => $id, // ID of a page, post, or custom type
			'post_type' => 'event-speaker'
		  );
		$query = new WP_Query($args);
		$result = $query->get_posts();
		if(empty($result)) return false;
		$speaker = $result[0];
		
		return $speaker;
	}

    public function add_back_button( $post ) {
        if( $post->post_type == 'event-speaker' )
            echo "<a class='button button-primary button-large' href='edit.php?post_type=event-speaker' id='my-custom-header-link'>" . __('Back', 'ctx-theme') . "</a>";
    }

    
}
    
SpeakerAdmin::register();