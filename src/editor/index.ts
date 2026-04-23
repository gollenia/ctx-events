import { unregisterBlockType } from '@wordpress/blocks';
import domReady from '@wordpress/dom-ready';
import { registerPlugin } from '@wordpress/plugins';

import './editor.scss';
import '../shared/icons/style.scss';
import '@events/emails/style.scss';

import bookingSidebar from './plugins/event/BookingSidebar';
import BookingStatus from './plugins/event/BookingStatus';
import DashboardButton from './plugins/event/DashboardButton';
import datetimeSelector from './plugins/event/datetime';
import locationSelector from './plugins/event/location';
import peopleSelector from './plugins/event/people';
import recurrenceSettings from './plugins/event/recurrence';
import './bindings';

const plugins = [
	{ name: 'event-select-datetime', component: datetimeSelector },
	{ name: 'event-select-location', component: locationSelector },
	{ name: 'event-select-people', component: peopleSelector },
	{ name: 'event-recurrence-settings', component: recurrenceSettings },
	{ name: 'event-booking-sidebar', component: bookingSidebar },
	{ name: 'event-booking-status', component: BookingStatus },
];

const currentType = (window as Window & { typenow?: string }).typenow;

if (currentType === 'ctx-event') {
	plugins.push({
		name: 'event-dashboard-button',
		component: DashboardButton,
	});
}

plugins.forEach((plugin) => {
	registerPlugin(plugin.name, {
		icon: null,
		render: plugin.component,
	});
});

domReady(() => {
	if (currentType !== 'ctx-event') {
		unregisterBlockType('events-manager/details');
	}

	if (currentType !== 'ctx-event') {
		unregisterBlockType('events-manager/booking');
	}

	if (currentType !== 'ctx-event-location') {
		unregisterBlockType('events-manager/locationeditor');
	}

	if (currentType !== 'ctx-bookingform' && currentType !== 'ctx-attendeeform') {
		unregisterBlockType('events-manager/form-container');
	}
});
