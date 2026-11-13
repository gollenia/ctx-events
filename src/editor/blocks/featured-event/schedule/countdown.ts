import { __, sprintf } from '@wordpress/i18n';

const SECOND_IN_MS = 1000;
const MINUTE_IN_SECONDS = 60;
const HOUR_IN_SECONDS = 60 * MINUTE_IN_SECONDS;
const DAY_IN_SECONDS = 24 * HOUR_IN_SECONDS;

/** WordPress sprintf does not support zero padding on positional numbers. */
function paddedFormat(format: string, ...values: Array<string | number>): string {
	const formatValues = sprintf as (template: string, ...args: Array<string | number>) => string;
	return formatValues(format.replace(/%(\d+)\$02d/g, '%$1$s'), ...values);
}

const pad = (value: number) => String(value).padStart(2, '0');

export function formatCountdownLabel(targetIso: string, now = Date.now()): string {
	const targetMs = new Date(targetIso).getTime();
	if (Number.isNaN(targetMs)) return '';

	const remaining = Math.ceil((targetMs - now) / SECOND_IN_MS);
	if (remaining <= 0) return __('Started', 'ctx-events');

	const days = Math.floor(remaining / DAY_IN_SECONDS);
	const hours = Math.floor((remaining % DAY_IN_SECONDS) / HOUR_IN_SECONDS);
	const minutes = Math.floor((remaining % HOUR_IN_SECONDS) / MINUTE_IN_SECONDS);
	const seconds = remaining % MINUTE_IN_SECONDS;

	if (remaining > DAY_IN_SECONDS) {
		return paddedFormat(
			/* translators: 1: days, 2: hours */
			__('in %1$d days %2$02d hours', 'ctx-events'), days, pad(hours),
		);
	}
	if (remaining > HOUR_IN_SECONDS) {
		return paddedFormat(
			/* translators: 1: hours, 2: minutes, 3: seconds */
			__('in %1$02d:%2$02d:%3$02d Hours', 'ctx-events'),
			pad(days * 24 + hours), pad(minutes), pad(seconds),
		);
	}
	if (remaining > MINUTE_IN_SECONDS) {
		return paddedFormat(
			/* translators: 1: minutes, 2: seconds */
			__('in %1$02d:%2$02d Minutes', 'ctx-events'), pad(minutes), pad(seconds),
		);
	}
	return sprintf(__('in %d Seconds', 'ctx-events'), remaining);
}
