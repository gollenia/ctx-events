import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';
import Countdown from './Countdown';
import type { CountdownAttributes } from './types';

domReady(() => {
	for (const element of document.querySelectorAll<HTMLElement>('.ctx-events-countdown')) {
		const raw = element.dataset['attributes'];
		if (!raw) continue;
		try {
			const attributes = JSON.parse(raw) as CountdownAttributes;
			createRoot(element).render(<Countdown attributes={attributes} />);
		} catch {
			// Ignore invalid block data without preventing other countdowns from loading.
		}
	}
});
