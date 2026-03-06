import { createRoot } from '@wordpress/element';
import GatewayTable from './GatewayTable';
import './style.scss';

export function initGatewayAdmin(rootElement: HTMLElement | null) {
	if (!rootElement) return;

	const root = createRoot(rootElement);
	root.render(<GatewayTable />);
}
