<?php

namespace Contexis\Events\Core\Utilities;

class EventScope {
	public static function get_all(): array {
		global $wp_locale;
		$start_of_week = get_option('start_of_week');
		$end_of_week_name = $start_of_week > 0 ? $wp_locale->get_weekday($start_of_week-1) : $wp_locale->get_weekday(6);
		$start_of_week_name = $wp_locale->get_weekday($start_of_week);
		return [
			'all' => __('All events','events'),
			'future' => __('Future events','events'),
			'past' => __('Past events','events'),
			'today' => __('Today\'s events','events'),
			'tomorrow' => __('Tomorrow\'s events','events'),
			'week' => sprintf(__('Events this whole week (%s to %s)','events'), $wp_locale->get_weekday_abbrev($start_of_week_name), $wp_locale->get_weekday_abbrev($end_of_week_name)),
			'this-week' => sprintf(__('Events this week (today to %s)','events'), $wp_locale->get_weekday_abbrev($end_of_week_name)),
			'month' => __('Events this month','events'),
			'this-month' => __('Events this month (today onwards)', 'events'),
			'next-month' => __('Events next month','events'),
			'1-months'  => __('Events current and next month','events'),
			'2-months'  => __('Events within 2 months','events'),
			'3-months'  => __('Events within 3 months','events'),
			'6-months'  => __('Events within 6 months','events'),
			'12-months' => __('Events within 12 months','events')
		];
	}
}