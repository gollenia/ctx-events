import { createRoot } from '@wordpress/element';

import RecurringEventsPage from './RecurringEventsPage';

export function initRecurringEventsAdmin(rootElement: HTMLElement): void {
	createRoot(rootElement).render(<RecurringEventsPage />);
}
