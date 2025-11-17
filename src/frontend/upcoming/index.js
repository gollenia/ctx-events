import { createRoot } from '@wordpress/element';
import Upcoming from './upcoming';

function initUpcoming(className) {
	const upcomingBlocks = document.getElementsByClassName(className);
	if (!upcomingBlocks) return;
	Array.from(upcomingBlocks).forEach((element) => {
		const attributes = JSON.parse(element.dataset.attributes);
		createRoot(element).render(<Upcoming attributes={{ ...attributes }} />);
	});
}
export default initUpcoming;
