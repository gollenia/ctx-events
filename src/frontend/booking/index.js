import { createRoot } from '@wordpress/element';
import Booking from './index.js';
import { publish } from './modules/events.js';

function initBooking() {
	const rootElement = document.getElementById('booking_app');
	const bookingButtons = document.getElementsByClassName(
		'wp-block-events-manager-booking',
	);

	if (rootElement) {
		const root = createRoot(rootElement);

		root.render(<Booking post={rootElement.dataset.post} open={false} />, root);

		for (const item of bookingButtons) {
			item.addEventListener('click', () => {
				publish('showBooking', true);
			});
		}
	}
}

export default initBooking;
