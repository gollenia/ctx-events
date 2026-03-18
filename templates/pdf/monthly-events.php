<?php
/**
 * @var array<string, array{timestamp: string, count: int, name: string, weekday: int, events: array<int, array<string, mixed>>, is_sunday: bool}> $days
 * @var string $mode
 * @var bool $showEmptyDays
 * @var \DateTimeImmutable $start
 * @var \DateTimeImmutable $end
 * @var string $title
 */
?>
<style>
	body {
		font-family: sans-serif;
		font-size: 11pt;
		color: #1f2933;
	}

	.pdf-header {
		margin-bottom: 18px;
		padding-bottom: 8px;
		border-bottom: 1px solid #d8dee4;
	}

	.pdf-title {
		font-size: 22pt;
		font-weight: bold;
		margin: 0 0 4px;
	}

	.pdf-subtitle {
		font-size: 10pt;
		color: #52606d;
	}

	.program-table {
		width: 100%;
		border-collapse: collapse;
	}

	.program-table td {
		vertical-align: top;
		padding: 10px 0;
		border-bottom: 1px solid #e5e7eb;
	}

	.day-cell {
		width: 70px;
		padding-right: 16px;
	}

	.day-number {
		font-size: 20pt;
		font-weight: bold;
		line-height: 1;
	}

	.day-name {
		font-size: 9pt;
		text-transform: uppercase;
		color: #6b7280;
		margin-top: 4px;
	}

	.event-item {
		margin-bottom: 12px;
	}

	.event-item:last-child {
		margin-bottom: 0;
	}

	.event-title {
		font-size: 12pt;
		font-weight: bold;
		margin-bottom: 2px;
	}

	.event-meta {
		font-size: 9pt;
		color: #52606d;
		margin-bottom: 3px;
	}

	.event-flag {
		font-size: 9pt;
		font-style: italic;
		color: #7c3aed;
		margin-bottom: 3px;
	}

	.event-excerpt {
		font-size: 10pt;
		color: #1f2933;
	}

	.empty-text {
		color: #9aa5b1;
		font-style: italic;
	}
</style>

<div class="pdf-header">
	<div class="pdf-title"><?php echo esc_html($title); ?></div>
	<div class="pdf-subtitle">
		<?php echo esc_html(wp_date(get_option('date_format'), $start->getTimestamp())); ?>
		<?php if ($start->format('Y-m-d') !== $end->format('Y-m-d')) : ?>
			<?php echo esc_html(' - ' . wp_date(get_option('date_format'), $end->getTimestamp())); ?>
		<?php endif; ?>
	</div>
</div>

<table class="program-table">
	<tbody>
		<?php foreach ($days as $day) : ?>
			<?php if (!$showEmptyDays && $day['events'] === []) : ?>
				<?php continue; ?>
			<?php endif; ?>
			<tr>
				<td class="day-cell">
					<div class="day-number"><?php echo esc_html((string) $day['count']); ?></div>
					<div class="day-name"><?php echo esc_html((string) $day['name']); ?></div>
				</td>
				<td>
					<?php if ($day['events'] === []) : ?>
						<div class="empty-text"><?php esc_html_e('No events', 'ctx-events'); ?></div>
					<?php else : ?>
						<?php foreach ($day['events'] as $event) : ?>
							<div class="event-item">
								<div class="event-title"><?php echo esc_html((string) $event['title']); ?></div>
								<?php if (!empty($event['isContinuation'])) : ?>
									<div class="event-flag"><?php esc_html_e('Continues', 'ctx-events'); ?></div>
								<?php elseif (!empty($event['dateLabel']) && is_string($event['dateLabel'])) : ?>
									<div class="event-flag"><?php echo esc_html($event['dateLabel']); ?></div>
								<?php endif; ?>
								<div class="event-meta">
									<?php
									$meta = array_filter([
										is_string($event['timeLabel'] ?? null) ? $event['timeLabel'] : '',
										is_string($event['location'] ?? null) ? $event['location'] : '',
										is_string($event['person'] ?? null) ? $event['person'] : '',
									]);
									echo esc_html(implode(' | ', $meta));
									?>
								</div>
								<?php if (!empty($event['excerpt']) && is_string($event['excerpt'])) : ?>
									<div class="event-excerpt"><?php echo esc_html($event['excerpt']); ?></div>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
