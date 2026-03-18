import { createRoot } from '@wordpress/element';
import Upcoming from './upcoming';

function initUpcoming(className: string) {
	const upcomingBlocks = document.getElementsByClassName(className);
	if (!upcomingBlocks) return;

	Array.from(upcomingBlocks).forEach((element) => {
		const rawAttributes = element.getAttribute('data-attributes');
		if (!rawAttributes) {
			return;
		}

		const attributes = JSON.parse(rawAttributes) as Record<string, unknown>;
		createRoot(element).render(<Upcoming attributes={attributes} />);
	});
}

export default initUpcoming;
