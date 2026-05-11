import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';
import Booking from './Booking';

domReady(() => {
	const rootElement = document.getElementById('booking_app');
	if (!rootElement) return;

	const root = createRoot(rootElement);

	root.render(<Booking />);
});
