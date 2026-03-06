import { createRoot } from '@wordpress/element';

import FormList from './FormList';
//import './style.scss';

export function initFormList(rootElement: HTMLElement | null): void {
	if (!rootElement) return;

	const root = createRoot(rootElement);
	root.render(<FormList />);
}
