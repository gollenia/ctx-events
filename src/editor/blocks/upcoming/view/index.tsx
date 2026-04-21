import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';
import { ensureIcons } from '../../../../shared/icons/ensureIcons';
import type { UpcomingAttributes } from './types';
import Upcoming from './Upcoming';

const UPCOMING_ICON_NAMES = ['date', 'time', 'audience', 'location', 'warning'];

async function initUpcoming(className = 'events-upcoming-block') {
	const upcomingBlocks = document.getElementsByClassName(className);

	if (upcomingBlocks.length === 0) {
		return;
	}

	await ensureIcons(UPCOMING_ICON_NAMES);

	Array.from(upcomingBlocks).forEach((element) => {
		const rawAttributes = element.getAttribute('data-attributes');
		if (!rawAttributes) {
			return;
		}

		const attributes = JSON.parse(rawAttributes) as UpcomingAttributes;
		createRoot(element).render(<Upcoming attributes={attributes} />);
	});
}

domReady(() => {
	initUpcoming();
});
