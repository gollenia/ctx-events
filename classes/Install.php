<?php

namespace Contexis\Events\Core;

use Contexis\Events\PostTypes\EventPost;
use Contexis\Events\PostTypes\LocationPost;

class Install {

	public static function intallation_error_notice($message = 'An unknown error occured') {
		if(!class_exists('\IntlDateFormatter')) {
			$message = __('The Events Manager plugin requires the PHP Intl extension to be installed and enabled on your server. Please contact your hosting provider to enable it.', 'events-manager');
		}
		
		$class = 'notice notice-error';
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}
	
	public static function deactivate_plugin() {
		if ( !is_plugin_active('events/events.php') ) return;
		deactivate_plugins('events/events.php');
		unset($_GET['activate']);
	}

	public static function uninstall() {
		global $wpdb;
	
		$post_ids = $wpdb->get_col('SELECT ID FROM '.$wpdb->posts." WHERE post_type IN ('".EventPost::POST_TYPE."','".LocationPost::POST_TYPE."','event-recurring')");
		foreach($post_ids as $post_id){
			wp_delete_post($post_id);
		}
		
		$cat_terms = get_terms(EventPost::CATEGORIES, array('hide_empty'=>false));
		foreach($cat_terms as $cat_term){
			wp_delete_term($cat_term->term_id, EventPost::CATEGORIES);
		}
		$tag_terms = get_terms(EventPost::TAGS, array('hide_empty'=>false));
		foreach($tag_terms as $tag_term){
			wp_delete_term($tag_term->term_id, EventPost::TAGS);
		}
		
	
		$wpdb->query('DROP TABLE '.EM_BOOKINGS_TABLE);
		$wpdb->query('DROP TABLE '.EM_META_TABLE);
		
		$wpdb->query('DELETE FROM '.$wpdb->options.' WHERE option_name LIKE \'em_%\' OR option_name LIKE \'dbem_%\'');
		
		wp_safe_redirect(admin_url('plugins.php?deactivate=true'));
		exit();
	}
}