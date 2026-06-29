import { createRoot } from '@wordpress/element';
import CouponList from './CouponList';

export function initCouponList(rootElement: HTMLElement | null): void {
	if (!rootElement) return;

	const root = createRoot(rootElement);
	root.render(<CouponList />);
}
