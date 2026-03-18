<?php

$selected_event = isset($attributes['selectedEvent']) ? (int) $attributes['selectedEvent'] : 0;
$fallback_event = get_post_type(get_the_ID()) === 'ctx-event' ? (int) get_the_ID() : 0;
$event_id = $selected_event ?: $fallback_event;

if ($event_id <= 0) {
	return;
}

$event_post = get_post($event_id);

if (!$event_post instanceof WP_Post || $event_post->post_type !== 'ctx-event') {
	return;
}

$layout = isset($attributes['layout']) && is_string($attributes['layout'])
	? $attributes['layout']
	: 'split';
$show_excerpt = filter_var($attributes['showExcerpt'] ?? true, FILTER_VALIDATE_BOOLEAN);
$show_location = filter_var($attributes['showLocation'] ?? true, FILTER_VALIDATE_BOOLEAN);
$show_button = filter_var($attributes['showButton'] ?? true, FILTER_VALIDATE_BOOLEAN);
$button_text = isset($attributes['buttonText']) && is_string($attributes['buttonText'])
	? $attributes['buttonText']
	: '';

$start = (string) get_post_meta($event_id, '_event_start', true);
$end = (string) get_post_meta($event_id, '_event_end', true);
$location_id = (int) get_post_meta($event_id, '_location_id', true);
$location_name = $location_id > 0 ? get_the_title($location_id) : '';
$excerpt = has_excerpt($event_id) ? get_the_excerpt($event_id) : wp_trim_words(wp_strip_all_tags((string) $event_post->post_content), 26);
$image_url = get_the_post_thumbnail_url($event_id, 'large');
$permalink = get_permalink($event_id);

$date_label = '';
if ($start !== '') {
	$start_timestamp = strtotime($start);
	$end_timestamp = $end !== '' ? strtotime($end) : false;

	if ($start_timestamp) {
		$date_label = wp_date(get_option('date_format'), $start_timestamp);
		if ($end_timestamp && wp_date('Ymd', $start_timestamp) !== wp_date('Ymd', $end_timestamp)) {
			$date_label .= ' - ' . wp_date(get_option('date_format'), $end_timestamp);
		}
	}
}

$wrapper_attributes = get_block_wrapper_attributes([
	'class' => sprintf('ctx-event-hero is-layout-%s', sanitize_html_class($layout)),
]);
?>

<section <?php echo $wrapper_attributes; ?>>
	<div class="ctx-event-hero__media">
		<?php if ($image_url) : ?>
			<img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr(get_the_title($event_id)); ?>" />
		<?php else : ?>
			<div class="ctx-event-hero__media-placeholder"><?php esc_html_e('No event image', 'ctx-events'); ?></div>
		<?php endif; ?>
	</div>
	<div class="ctx-event-hero__content">
		<?php if ($date_label !== '') : ?>
			<div class="ctx-event-hero__eyebrow"><?php echo esc_html($date_label); ?></div>
		<?php endif; ?>

		<h2 class="ctx-event-hero__title">
			<a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html(get_the_title($event_id)); ?></a>
		</h2>

		<?php if ($show_location && $location_name !== '') : ?>
			<div class="ctx-event-hero__meta"><?php echo esc_html($location_name); ?></div>
		<?php endif; ?>

		<?php if ($show_excerpt && $excerpt !== '') : ?>
			<p class="ctx-event-hero__excerpt"><?php echo esc_html($excerpt); ?></p>
		<?php endif; ?>

		<?php if ($show_button) : ?>
			<div class="ctx-event-hero__actions">
				<a class="ctx-event-hero__button" href="<?php echo esc_url($permalink); ?>">
					<?php echo esc_html($button_text !== '' ? $button_text : __('View event', 'ctx-events')); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>
