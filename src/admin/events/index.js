import { createRoot } from '@wordpress/element';
import EventsList from './EventsList.js';
import './style.scss';

export function initEventsList(rootElement) {
	if (!rootElement) return;

	const root = createRoot(rootElement);
	root.render(<EventsList />);
}
