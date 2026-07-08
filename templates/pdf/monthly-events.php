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
		border-bottom: 1px solid #e5e7eb;
	}

	.program-table tr.weekday-7 td {
		background-color: #f3f4f6;
	}

	.day-cell {
		width: 1px;
		padding: 3px;
		white-space: nowrap;
	}

	.right {
		text-align: right;
	}

	.padding {
		padding: 3px;
	}
	

	.day-number {
		font-weight: bold;
		line-height: 1;
		display: inline-block;
	}

	.day-name {
		text-transform: uppercase;
		color: #6b7280;
	}

	.event-item {
		width: 100%;
		border-collapse: collapse;
	}

	.event-item:last-child {
		margin-bottom: 0;
	}

	.event-item td {
		border: 0;
		
	}

	.event-main {
		padding-right: 12px;
	}

	.event-time {
		width: 72px;
		text-align: right;
		white-space: nowrap;
		color: #52606d;
	}

	.event-title {
		font-weight: bold;
	}

	.border{
		border-bottom: 1px solid #bbb;
	}

	.event-meta {
		
		color: #52606d;
		
	}

	.event-flag {
		
		font-style: italic;
		color: #7c3aed;
		
	}

	.event-excerpt {
		
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
			<tr class="weekday-<?php echo esc_attr((string) $day['weekday']); ?>">
				<td class="day-cell right padding">
					<span class="day-number"><?php echo esc_html((string) $day['count']); ?></span>
				</td>
				<td class="day-cell padding">
					<span class="day-name"><?php echo esc_html((string) $day['name']); ?></span>
				</td>
				<td class="padding">
					<?php if ($day['events'] === []) : ?>
						<div class="empty-text"></div>
					<?php else : ?>
						<?php foreach ($day['events'] as $event) : ?>
							<table class="event-item">
								<tr>
									<td class="event-main">
										<span class="event-title"><?php echo esc_html((string) $event['title']); ?></span>
										<?php if (!empty($event['isContinuation'])) : ?>
											<span class="event-flag"><?php esc_html_e('Continues', 'ctx-events'); ?></span>
										<?php elseif (!empty($event['dateLabel']) && is_string($event['dateLabel'])) : ?>
											<span class="event-flag"><?php echo esc_html($event['dateLabel']); ?></span>
										<?php endif; ?>
										<span class="event-meta">
											<?php
											$meta = array_filter([
												is_string($event['location'] ?? null) ? $event['location'] : '',
												is_string($event['person'] ?? null) ? $event['person'] : '',
											]);
											echo esc_html(implode(' | ', $meta));
											?>
										</span>
									</td>
									<td class="event-time">
										<?php echo esc_html(is_string($event['timeLabel'] ?? null) ? $event['timeLabel'] : ''); ?>
									</td>
								</tr>
							</table>
						<?php endforeach; ?>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
