import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';
import { ensureIcons } from '../../shared/icons/ensureIcons';
import Booking from './Booking';

domReady(() => {
	const rootElement = document.getElementById('booking_app');
	if (!rootElement) return;

	const root = createRoot(rootElement);
	ensureIcons(['delete']).finally(() => {
		root.render(<Booking />);
	});
});
