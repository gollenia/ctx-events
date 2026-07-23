import domReady from '@wordpress/dom-ready';
import { __, sprintf } from '@wordpress/i18n';

const SECOND_IN_MS = 1000;
const MINUTE_IN_MS = 60 * SECOND_IN_MS;
const HOUR_IN_MS = 60 * MINUTE_IN_MS;
const DAY_IN_MS = 24 * HOUR_IN_MS;

function formatCountdownLabel(targetIso: string): string {
	const targetMs = new Date(targetIso).getTime();
	const remainingMs = targetMs - Date.now();

	if (Number.isNaN(targetMs)) {
		return '';
	}

	if (remainingMs <= 0) {
		return __('Started', 'ctx-events');
	}

	const days = Math.floor(remainingMs / DAY_IN_MS);
	const hours = Math.floor((remainingMs % DAY_IN_MS) / HOUR_IN_MS);
	const minutes = Math.floor((remainingMs % HOUR_IN_MS) / MINUTE_IN_MS);
	const seconds = Math.floor((remainingMs % MINUTE_IN_MS) / SECOND_IN_MS);

	if (remainingMs > DAY_IN_MS) {
		return sprintf(
			/* translators: 1: days, 2: hours */
			__('in %1$d days %2$02d hours', 'ctx-events'),
			days,
			hours,
		);
	}

	if (remainingMs > HOUR_IN_MS) {
		return sprintf(
			/* translators: 1: hours, 2: minutes, 3: seconds */
			__('in %1$02d:%2$02d:%3$02d Hours', 'ctx-events'),
			days * 24 + hours,
			minutes,
			seconds,
		);
	}

	if (remainingMs > MINUTE_IN_MS) {
		return sprintf(
			/* translators: 1: minutes, 2: seconds */
			__('in %1$02d:%2$02d Minutes', 'ctx-events'),
			Math.floor(remainingMs / MINUTE_IN_MS),
			seconds,
		);
	}

	return sprintf(
		/* translators: %d: remaining seconds */
		__('in %d Seconds', 'ctx-events'),
		Math.max(1, Math.ceil(remainingMs / SECOND_IN_MS)),
	);
}

function initCountdown(element: HTMLElement): void {
	const targetIso = element.dataset.ctxFeaturedCountdownTarget ?? '';
	if (!targetIso) {
		return;
	}

	const render = () => {
		const label = formatCountdownLabel(targetIso);
		if (label) {
			element.textContent = label;
		}
	};

	render();
	window.setInterval(render, SECOND_IN_MS);
}

domReady(() => {
	const countdowns = document.querySelectorAll<HTMLElement>(
		'[data-ctx-featured-countdown="true"]',
	);

	for (const countdown of countdowns) {
		initCountdown(countdown);
	}
});
