import domReady from '@wordpress/dom-ready';
import { formatCountdownLabel } from './countdown';

const SECOND_IN_MS = 1000;

function initCountdown(element: HTMLElement): void {
	const targetIso = element.dataset['ctxFeaturedCountdownTarget'] ?? '';
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
