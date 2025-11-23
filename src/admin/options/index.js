import { createRoot } from '@wordpress/element';
import Options from './Options.js';
//import './style.scss';

export function initOptionsAdmin(rootElement) {
	if (!rootElement) return;

	const root = createRoot(rootElement);
	root.render(<Options />);
}
