import { createRoot } from '@wordpress/element';
import BookingsList from './BookingsList';
import '../shared/action-modal.scss';
import './style.scss';

export function initBookingsAdmin(rootElement: HTMLElement | null) {
	if (!rootElement) return;

	const root = createRoot(rootElement);
	root.render(<BookingsList />);
}
