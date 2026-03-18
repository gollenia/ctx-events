import domReady from '@wordpress/dom-ready';

import { initBookingsAdmin } from './bookings/index.tsx';
import { initEmailAdmin } from './emails/index.tsx';
import { initEventsList } from './events/index.tsx';
import { initFormList } from './forms/index.tsx';
import { initGatewayAdmin } from './gateways/index.tsx';
import { initOptionsAdmin } from './options';
import './style.scss';

type AdminMount = {
	id: string;
	init: (element: HTMLElement) => void;
};

domReady(() => {
	const mounts: AdminMount[] = [
		{ id: 'ctx-bookings-admin', init: initBookingsAdmin },
		{ id: 'ctx-gateways-admin', init: initGatewayAdmin },
		{ id: 'ctx-email-admin', init: initEmailAdmin },
		{ id: 'ctx-options-admin', init: initOptionsAdmin },
		{ id: 'ctx-events-list', init: initEventsList },
		{ id: 'ctx-forms-admin', init: initFormList },
	];

	mounts.forEach(({ id, init }) => {
		const element = document.getElementById(id);
		if (!element) {
			return;
		}

		init(element);
	});
});
