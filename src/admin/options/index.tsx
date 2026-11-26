import { createRoot } from '@wordpress/element';

import Options from './Options';

export function initOptionsAdmin(rootElement: HTMLElement | null) {
	if (!rootElement) {
		return;
	}

	const root = createRoot(rootElement);
	root.render(
		<Options
			codeEditors={JSON.parse(rootElement.dataset.codeEditor ?? '{}')}
		/>,
	);
}
