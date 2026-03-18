<?php

$title = isset($attributes['title']) && is_string($attributes['title'])
	? $attributes['title']
	: '';
$button_text = isset($attributes['buttonText']) && is_string($attributes['buttonText'])
	? $attributes['buttonText']
	: '';
$export_mode = isset($attributes['exportMode']) && is_string($attributes['exportMode'])
	? $attributes['exportMode']
	: 'month';
$periods_ahead = isset($attributes['periodsAhead'])
	? (int) $attributes['periodsAhead']
	: (isset($attributes['monthsAhead']) ? (int) $attributes['monthsAhead'] : 12);
$periods_ahead = max(1, min(24, $periods_ahead));
$show_empty_days = array_key_exists('showEmptyDays', $attributes)
	? filter_var($attributes['showEmptyDays'], FILTER_VALIDATE_BOOLEAN)
	: true;
$category = isset($attributes['category'])
	? (int) $attributes['category']
	: (isset($attributes['featuredCategory']) ? (int) $attributes['featuredCategory'] : 0);
$field_id = wp_unique_id('ctx-program-pdf-');

$action = rest_url('events/v3/events/monthly-pdf');
$wrapper_attributes = get_block_wrapper_attributes([
	'class' => 'ctx-program-pdf-export',
]);
?>

<div <?php echo $wrapper_attributes; ?>>
	<?php if ($title !== '') : ?>
		<div class="ctx-program-pdf-export__title"><?php echo esc_html($title); ?></div>
	<?php endif; ?>

	<form class="ctx-program-pdf-export__form" action="<?php echo esc_url($action); ?>" method="get">
		<label class="screen-reader-text" for="<?php echo esc_attr($field_id); ?>">
			<?php esc_html_e('Choose period', 'ctx-events'); ?>
		</label>
		<input type="hidden" name="mode" value="<?php echo esc_attr($export_mode); ?>" />
		<input type="hidden" name="show_empty_days" value="<?php echo $show_empty_days ? '1' : '0'; ?>" />
		<select
			class="ctx-program-pdf-export__select"
			name="offset"
			id="<?php echo esc_attr($field_id); ?>"
		>
			<?php for ($offset = 0; $offset < $periods_ahead; $offset++) : ?>
				<?php
				$date = new DateTimeImmutable('now', wp_timezone());
				$label = '';
				if ($export_mode === 'week') {
					$date = $date->modify('monday this week');
					if ($offset > 0) {
						$date = $date->modify(sprintf('+%d weeks', $offset));
					}
					$end_date = $date->modify('sunday this week');
					$label = wp_date(get_option('date_format'), $date->getTimestamp()) . ' - ' . wp_date(get_option('date_format'), $end_date->getTimestamp());
				} elseif ($export_mode === 'year') {
					$date = $date->modify('first day of January');
					if ($offset > 0) {
						$date = $date->modify(sprintf('+%d years', $offset));
					}
					$label = wp_date('Y', $date->getTimestamp());
				} else {
					$date = $date->modify('first day of this month');
					if ($offset > 0) {
						$date = $date->modify(sprintf('+%d months', $offset));
					}
					$label = wp_date('F Y', $date->getTimestamp());
				}
				?>
				<option value="<?php echo esc_attr((string) $offset); ?>">
					<?php echo esc_html($label); ?>
				</option>
			<?php endfor; ?>
		</select>

		<?php if ($category > 0) : ?>
			<input type="hidden" name="category" value="<?php echo esc_attr((string) $category); ?>" />
		<?php endif; ?>

		<button type="submit" class="ctx-program-pdf-export__button">
			<?php echo esc_html($button_text !== '' ? $button_text : __('PDF herunterladen', 'ctx-events')); ?>
		</button>
	</form>
</div>
