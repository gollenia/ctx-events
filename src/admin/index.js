import domReady from '@wordpress/dom-ready';
import './admin.scss';
import { initBookingsAdmin } from './bookings/index.js';
import { initGatewayAdmin } from './gateways/index.js';

domReady(() => {
	const mounts = [
		{ id: 'ctx-bookings-admin', init: initBookingsAdmin },
		{ id: 'ctx-gateways-admin', init: initGatewayAdmin },
	];

	mounts.forEach(({ id, init }) => {
		const element = document.getElementById(id);
		if (!element) return;
		init(element);
	});
});
