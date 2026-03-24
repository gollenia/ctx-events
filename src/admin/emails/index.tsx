import { createRoot } from '@wordpress/element';
import EmailsTable from './EmailsTable';
import './style.scss';
import '@events/emails/style.scss';

export function initEmailAdmin(rootElement: HTMLElement | null) {
	if (!rootElement) return;

	const root = createRoot(rootElement);
	root.render(<EmailsTable />);
}
