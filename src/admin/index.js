import domReady from '@wordpress/dom-ready';
import './style.scss';
import { initBookingsAdmin } from './bookings/index.tsx';
import { initEventsList } from './events/index.tsx';
import { initFormList } from './forms/index.tsx';
import { initGatewayAdmin } from './gateways/index.tsx';
import { initOptionsAdmin } from './options/index.js';

console.log('ctx-events admin loaded');
domReady(() => {
	const mounts = [
		{ id: 'ctx-bookings-admin', init: initBookingsAdmin },
		{ id: 'ctx-gateways-admin', init: initGatewayAdmin },
		{ id: 'ctx-options-admin', init: initOptionsAdmin },
		{ id: 'ctx-events-list', init: initEventsList },
		{ id: 'ctx-forms-admin', init: initFormList },
	];

	mounts.forEach(({ id, init }) => {
		const element = document.getElementById(id);
		if (!element) return;
		init(element);
	});
});
