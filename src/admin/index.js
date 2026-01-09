import domReady from '@wordpress/dom-ready';
import './style.scss';
import { initBookingsAdmin } from './bookings/index.js';
import { initGatewayAdmin } from './gateways/index.js';
import { initOptionsAdmin } from './options/index.js';
import { initEventsList } from './events/index.js';

console.log('ctx-events admin loaded');
domReady(() => {
	const mounts = [
		{ id: 'ctx-bookings-admin', init: initBookingsAdmin },
		{ id: 'ctx-gateways-admin', init: initGatewayAdmin },
		{ id: 'ctx-options-admin', init: initOptionsAdmin },
		{ id: 'ctx-events-list', init: initEventsList },
	];
	console.log('Admin mounts:', mounts);
	mounts.forEach(({ id, init }) => {
		const element = document.getElementById(id);
		if (!element) return;
		init(element);
	});
});
