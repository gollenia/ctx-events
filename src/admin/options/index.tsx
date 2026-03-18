import { createRoot } from '@wordpress/element';

import Options from './Options';

export function initOptionsAdmin(rootElement: Element | null) {
	if (!rootElement) {
		return;
	}

	const root = createRoot(rootElement);
	root.render(<Options />);
}
