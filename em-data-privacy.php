<?php
/**
 * Outputs a checkbox that can be used to obtain consent.
 * Complete nonsense, change!
 * @param EM_Event|EM_Location|EM_Booking|bool $EM_Object
 */
function em_data_privacy_consent_checkbox( $EM_Object = false ){
	if( !empty($EM_Object) && (!empty($EM_Object->booking_id) || !empty($EM_Object->post_id)) ) return; //already saved so consent was given at one point
	$label = get_option('dbem_data_privacy_consent_text');
	// buddyboss fix since bb v1.6.0
	if( has_filter( 'the_privacy_policy_link', 'bp_core_change_privacy_policy_link_on_private_network') ) $bb_fix = remove_filter('the_privacy_policy_link', 'bp_core_change_privacy_policy_link_on_private_network', 999999);
	// replace privacy policy text %s with link to policy page
	if( function_exists('get_the_privacy_policy_link') ) $label = sprintf($label, get_the_privacy_policy_link());
	// buddyboss unfix since bb v1.6.0
	if( !empty($bb_fix) ) add_filter( 'the_privacy_policy_link', 'bp_core_change_privacy_policy_link_on_private_network', 999999, 2 );
	// check if consent was previously given and check box if true

    if( empty($checked) && !empty($_REQUEST['data_privacy_consent']) ) $checked = true;
    // output checkbox
	?>
    <p class="input-group input-checkbox input-field-data_privacy_consent">
		<label>
			<input type="checkbox" name="data_privacy_consent" value="1" <?php if( !empty($checked) ) echo 'checked="checked"'; ?>>
			<?php echo $label; ?>
		</label>
        <br style="clear:both;">
	</p>
	<?php
}

function em_data_privacy_consent_hooks(){
	//BOOKINGS
	if( get_option('dbem_data_privacy_consent_bookings') == 1 || ( get_option('dbem_data_privacy_consent_bookings') == 2 && !is_user_logged_in() ) ){
	    add_action('em_booking_form_footer', 'em_data_privacy_consent_checkbox', 9, 0); //supply 0 args since arg is $EM_Event and callback will think it's an event submission form
		add_filter('em_booking_validate', 'em_data_privacy_consent_booking_validate', 10, 2);
		
	}
	//EVENTS
	if( get_option('dbem_data_privacy_consent_events') == 1 || ( get_option('dbem_data_privacy_consent_events') == 2 && !is_user_logged_in() ) ){
		add_action('em_front_event_form_footer', 'em_data_privacy_consent_event_checkbox', 9, 1);
		/**
		 * Wrapper function in case old overriden templates didn't pass the EM_Event object and depended on global value
		 * @param EM_Event $event
		 */
		
		add_action('em_event_get_post_meta', 'em_data_privacy_cpt_get_post', 10, 2);
		add_action('em_event_validate', 'em_data_privacy_cpt_validate', 10, 2);
	}
	//LOCATIONS
	if( get_option('dbem_data_privacy_consent_locations') == 1 || ( get_option('dbem_data_privacy_consent_events') == 2 && !is_user_logged_in() ) ){
		add_action('em_front_location_form_footer', 'em_data_privacy_consent_location_checkbox', 9, 1);	/**
		 * Wrapper function in case old overriden templates didn't pass the EM_Location object and depended on global value
		 * @param EM_Location $location
		 */
		function em_data_privacy_consent_location_checkbox( $location ){
			if( empty($location) ){ global $EM_Location; }
			else{ $EM_Location = $location ; }
			em_data_privacy_consent_checkbox($EM_Location);
		}
		add_action('em_location_get_post_meta', 'em_data_privacy_cpt_get_post', 10, 2);
		add_action('em_location_validate', 'em_data_privacy_cpt_validate', 10, 2);
		
	}
}
if( !is_admin() || ( defined('DOING_AJAX') && DOING_AJAX && !empty($_REQUEST['action']) && !in_array($_REQUEST['action'], array('booking_add_one')) ) ){
	add_action('init', 'em_data_privacy_consent_hooks');
}

/**
 * Validates a bookng to ensure consent is/was given.
 * @param bool $result
 * @param EM_Booking $EM_Booking
 * @return bool
 */
function em_data_privacy_consent_booking_validate( $result, $EM_Booking ){
	
    if( empty($EM_Booking->booking_meta['consent']) ){
	    $EM_Booking->add_error( sprintf(__('You must allow us to collect and store your data in order for us to process your booking.', 'events')) );
	    $result = false;
    }
    return $result;
}



/**
 * Save consent to event or location object
 * @param bool $result
 * @param EM_Event|EM_Location $EM_Object
 * @return bool
 */
function em_data_privacy_cpt_get_post($result, $EM_Object ){
	if( !empty($_REQUEST['data_privacy_consent']) ){
		if( get_class($EM_Object) == 'EM_Event' ){
			$EM_Object->event_attributes['_consent_given'] = 1;
			$EM_Object->get_location()->location_attributes['_consent_given'] = 1;
		}else{
			$EM_Object->location_attributes['_consent_given'] = 1;
		}
	}
    return $result;
}

/**
 * Validate the consent provided to events and locations.
 * @param bool $result
 * @param EM_Event|EM_Location $EM_Object
 * @return bool
 */
function em_data_privacy_cpt_validate( $result, $EM_Object ){
	if( !empty($EM_Object->post_id) ) return $result;
	
	$attributes = get_class($EM_Object) == 'EM_Event' ? 'event_attributes':'location_attributes';
	if( empty($EM_Object->{$attributes}['_consent_given']) ){
		$EM_Object->add_error( sprintf(__('Please check the consent box so we can collect and store your submitted information.', 'events')) );
		$result = false;
    }
	return $result;
}

