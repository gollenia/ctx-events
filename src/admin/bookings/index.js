import { createRoot } from '@wordpress/element';
import BookingTable from './BookingTable';
import './style.scss';

export function initBookingsAdmin(rootElement) {
	if (!rootElement) return;

	const root = createRoot(rootElement);
	root.render(<BookingTable />);
}
