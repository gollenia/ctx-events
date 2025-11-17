import { createRoot } from '@wordpress/element';
import GatewayTable from './GatewayTable.js';
import './style.scss';

export function initGatewayAdmin(rootElement) {
	if (!rootElement) return;

	const root = createRoot(rootElement);
	root.render(<GatewayTable />);
}
