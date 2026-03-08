import { createRoot } from '@wordpress/element';
import BookingsList from './BookingsList';
import './style.scss';

export function initBookingsAdmin(rootElement) {
	if (!rootElement) return;

	const root = createRoot(rootElement);
	root.render(<BookingsList />);
}
