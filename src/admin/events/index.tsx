import { createRoot } from '@wordpress/element';
import EventsList from './EventsList';
import '../shared/action-modal.scss';
import './style.scss';

export function initEventsList(rootElement: HTMLElement | null): void {
	if (!rootElement) return;

	const root = createRoot(rootElement);
	root.render(<EventsList />);
}
