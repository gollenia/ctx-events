<?php

namespace Contexis\Events\Forms;

use Contexis\Events\Collections\EventCollection;
use Contexis\Events\Models\Event;
use Contexis\Events\PostTypes\EventPost;

class FormPostAdmin {



	public static function init(){
		$instance = new self;
		add_filter( 'manage_bookingform_posts_columns',  [$instance, 'manage_posts_columns'] );
		add_action('em_bookings_table_export_options', array($instance, 'em_bookings_table_export_options')); //show booking form and ticket summary
		add_filter('em_bookings_table_rows_col', array($instance,'em_bookings_table_rows_col'),10,5);
		add_filter('em_bookings_table_cols_template', array($instance,'em_bookings_table_cols_template'),10,2);
		
		return $instance;
	}

	function manage_posts_columns($columns) {
		unset( $columns['date'] );
		$columns['type'] = __( 'Type', 'events' );
		return $columns;
	}

	public static function option_page() {
		echo "<div class='wrap'>";
		echo "<h1 class='wp-heading-inline' style='margin-bottom: 1rem'>" . __( 'Booking Forms', 'events' ) . "</h1>";
		?><a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=bookingform')); ?>" class="page-title-action"><?php _e("Create", "events") ?></a><?php
		$the_query = new \WP_Query( ['post_type' => 'bookingform', 'posts_per_page'=>-1] );
		?>
		<table class="wp-list-table widefat fixed striped table-view-list posts">
			<thead>
				<tr>
					<th><?php _e( 'Form Name', 'events' ); ?></th>
					<th><?php _e( 'Description', 'events' ); ?></th>
					<th><?php _e( 'Date', 'events' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				if ( $the_query->have_posts() ) { 
					while ( $the_query->have_posts() ) {
						$the_query->the_post();
						?>
						<tr>
							<td class="title column-title has-row-actions column-primary page-title"><strong><a class="row-title" href="<?php echo esc_url( admin_url( 'post.php?post=' . get_the_ID() . '&action=edit' ) ); ?>"><?php the_title(); ?></strong>
							<div class="row-actions">
								<span class="edit"><a href="<?php echo esc_url( admin_url( 'post.php?post=' . get_the_ID() . '&action=edit' ) ); ?>" aria-label="bearbeiten"><?php _e( 'Edit', 'events' ); ?></a> | </span>
								<span class="trash"><a class="submitdelete" href="<?php echo add_query_arg( ['action' => 'trash', 'post' => get_the_ID(), '_wpnonce'=>wp_create_nonce('trash-post_'.get_the_ID()) ], admin_url( 'post.php' ) ); ?>" aria-label="bearbeiten"><?php _e( 'Delete', 'events' ); ?></a></span>
							</div>
							</td>
							<td><?php echo the_excerpt(); ?></td>
							<td>
								<?php echo get_the_date(); ?>
							</td>
						</tr>
						<?php
					}
				} ?>
		</table>
		<?php
		echo "<h1 class='wp-heading-inline' style='margin-top: 2rem; margin-bottom: 1rem'>" . __( 'Attendee Forms', 'events' ) . "</h1>";
		?><a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=attendeeform')); ?>" class="page-title-action"><?php _e("Create", "events") ?></a><?php
		$the_query = new \WP_Query( ['post_type' => 'attendeeform', 'posts_per_page'=>-1] );
		
		?>
		<hr class="wp-header-end">
		<ul class="subsubsub">
			<li class="all"><a href="edit.php?post_type=post" class="current" aria-current="page">Alle <span class="count"><?php echo "($the_query->found_posts)" ?></span></a> |</li>
			<li class="unused"><a href="edit.php?post_status=publish&amp;post_type=post">Unbenutzte <span class="count">(9)</span></a> |</li>
			<li class="sticky"><a href="edit.php?post_type=post&amp;show_sticky=1">Oben gehalten <span class="count">(2)</span></a></li>
		</ul>
		<table class="wp-list-table widefat fixed striped table-view-list posts">
			<thead>
				<tr>
					<th><?php _e( 'Form Name', 'events' ); ?></th>
					<th><?php _e( 'Description', 'events' ); ?></th>
					<th><?php _e( 'Date', 'events' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				if ( $the_query->have_posts() ) {
					while ( $the_query->have_posts() ) {
						$the_query->the_post();
						?>
						<tr>
							<td class="title column-title has-row-actions column-primary page-title"><strong><a class="row-title" href="<?php echo esc_url( admin_url( 'post.php?post=' . get_the_ID() . '&action=edit' ) ); ?>"><?php the_title(); ?></strong>
							<div class="row-actions">
								<span class="edit"><a href="<?php echo esc_url( admin_url( 'post.php?post=' . get_the_ID() . '&action=edit' ) ); ?>" aria-label="bearbeiten"><?php _e( 'Edit', 'events' ); ?></a> | </span>
								<span class="trash"><a class="submitdelete" href="<?php echo add_query_arg( ['action' => 'trash', 'post' => get_the_ID(), '_wpnonce'=>wp_create_nonce('trash-post_'.get_the_ID()) ], admin_url( 'post.php' ) ); ?>" aria-label="bearbeiten"><?php _e( 'Delete', 'events' ); ?></a></span>
							</div>
							</td>
							<td><?php echo the_excerpt(); ?></td>
							<td>
								<?php echo get_the_date(); ?>
							</td>
						</tr>
						<?php
					}
				} ?>
		</table><?php
	}

	public function em_bookings_table_export_options(){
		?>
		<p><input type="checkbox" name="show_attendees" value="1" /><label><?php _e('Split bookings by attendee','events')?> </label>
		
		<script type="text/javascript">
			jQuery(document).ready(function($){
				$('#em-bookings-table-export-form input[name=show_attendees]').click(function(){
					$('#em-bookings-table-export-form input[name=show_tickets]').attr('checked',true);
					//copied from export_overlay_show_tickets function:
					$('#em-bookings-table-export-form .em-bookings-col-item-ticket').show();
					$('#em-bookings-table-export-form #em-bookings-export-cols-active .em-bookings-col-item-ticket input').val(1);
				});
				$('#em-bookings-table-export-form input[name=show_tickets]').change(function(){
					if( !this.checked ){
						$('#em-bookings-table-export-form input[name=show_attendees]').attr('checked',false);
					}
				});
			});
		</script>
		<?php
		
	}

	public function em_bookings_table_rows_col($column, $booking, $format){
		$EM_Form = BookingForm::get_form($booking->get_event()->event_id ?? null);

		if (!array_key_exists($column, $booking->registration ?? [])) {
			return '';
		}
		$field = $EM_Form->form_fields[$column];
    	$value = $EM_Form->get_formatted_value($field, $booking->registration[$column]);

    	return ($format == 'html' || empty($format)) ? esc_html($value) : $value;
	}
	
	public function em_bookings_table_cols_template($template, $bookings_table){
		$event = $bookings_table->event;
		$event_id = (!empty($event->event_id)) ? $event->event_id:false;
		$EM_Form = BookingForm::get_form($event_id);
		foreach($EM_Form->form_fields as $field_id => $field ){
		    if( $EM_Form->is_normal_field($field_id) ){ //user fields already handled, htmls shouldn't show
    			$template[$field_id] = $field['label'] ?? '';
		    }
		}
		return $template;
	}
	
}
FormPostAdmin::init();
