import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';
import Booking from './Booking';

function publish(eventName: string, data: unknown): void {
	document.dispatchEvent(new CustomEvent(eventName, { detail: data }));
}

domReady(() => {
	const rootElement = document.getElementById('booking_app');
	if (!rootElement) return;

	const postId = parseInt(rootElement.dataset.post ?? '0', 10);
	if (!postId) return;

	const root = createRoot(rootElement);
	root.render(<Booking postId={postId} />);

	for (const button of document.getElementsByClassName('wp-block-ctx-events-booking')) {
		button.addEventListener('click', () => publish('showBooking', true));
	}
});
