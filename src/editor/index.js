/**
 * WordPress dependencies
 */
import { registerBlockType, unregisterBlockType } from '@wordpress/blocks';
import domReady from '@wordpress/dom-ready';
import { registerPlugin } from '@wordpress/plugins';
import './editor.scss';

/**
 * Blocks dependencies.
 */
import * as booking from './blocks/booking/index.ts';

/**
 * Form dependencies.
 */
import * as couponEditor from './blocks/coupon-editor/index.ts';
import * as details from './blocks/details/index.ts';
import * as detailsAudience from './blocks/details-audience/index.ts';
import * as detailsDate from './blocks/details-date/index.ts';
import * as detailsItem from './blocks/details-item/index.ts';
import * as detailsLocation from './blocks/details-location/index.ts';
import * as detailsPerson from './blocks/details-person/index.ts';
import * as detailsPrice from './blocks/details-price/index.ts';
import * as detailsShutdown from './blocks/details-shutdown/index.ts';
import * as detailsSpaces from './blocks/details-spaces/index.ts';
import * as detailsTime from './blocks/details-time/index.ts';
import * as hero from './blocks/hero/index.ts';
import * as formCheckbox from './blocks/form/checkbox/index.ts';
import * as formContainer from './blocks/form/container/index.js';
import * as formCountry from './blocks/form/country/index.ts';
import * as formDate from './blocks/form/date/index.ts';
import * as formEmail from './blocks/form/email/index.ts';
import * as formHTML from './blocks/form/html/index.tsx';
import * as formPhone from './blocks/form/phone/index.ts';
import * as formRadio from './blocks/form/radio/index.ts';
import * as formSelect from './blocks/form/select/index.ts';
import * as formText from './blocks/form/text/index.ts';
import * as formTextarea from './blocks/form/textarea/index.ts';
import * as locationEditor from './blocks/location-editor/index.ts';
import * as programPdfExport from './blocks/program-pdf-export/index.ts';
import * as personEditor from './blocks/person-editor/index.ts';
import * as upcoming from './blocks/upcoming/index.ts';
import bookingSidebar from './plugins/event/BookingSidebar.js';
/**
 * Plugin dependencies.
 */
import BookingStatus from './plugins/event/BookingStatus.js';
import DashboardButton from './plugins/event/DashboardButton.js';
import datetimeSelector from './plugins/event/datetime.js';
import locationSelector from './plugins/event/location.js';
import peopleSelector from './plugins/event/people.js';
import recurrenceSettings from './plugins/event/recurrence.js';
import LocationAddress from './plugins/location/LocationAddress.js';
import personalData from './plugins/person/personal.js';

const blocks = [
	upcoming,
	booking,
	hero,
	formContainer,
	formText,
	formEmail,
	formTextarea,
	formDate,
	formCheckbox,
	formSelect,
	formCountry,
	formPhone,
	formRadio,
	formHTML,
	locationEditor,
	programPdfExport,
	couponEditor,
	details,
	detailsAudience,
	detailsDate,
	detailsLocation,
	detailsPerson,
	detailsPrice,
	detailsItem,
	detailsTime,
	detailsShutdown,
	detailsSpaces,
	personEditor,
];

const plugins = [
	{ name: 'event-location-address', component: LocationAddress },
	{ name: 'event-select-location', component: locationSelector },
	{ name: 'event-select-datetime', component: datetimeSelector },
	{ name: 'event-select-people', component: peopleSelector },
	{ name: 'event-personal-data', component: personalData },
	{ name: 'event-recurrence-settings', component: recurrenceSettings },
	{ name: 'event-booking-sidebar', component: bookingSidebar },
	{ name: 'event-booking-status', component: BookingStatus },
	{ name: 'event-dashboard-button', component: DashboardButton },
];

plugins.forEach((plugin) => {
	registerPlugin(plugin.name, {
		icon: null,
		render: plugin.component,
	});
});

blocks.forEach((block) => {
	if (!block) return;
	const { name, settings } = block;
	registerBlockType(name, settings);
});

domReady(() => {
	if (window.typenow !== 'ctx-event')
		unregisterBlockType('events-manager/details');
	if (window.typenow !== 'ctx-event')
		unregisterBlockType('events-manager/booking');
	if (window.typenow !== 'ctx-event-location')
		unregisterBlockType('events-manager/locationeditor');
	if (
		window.typenow !== 'ctx-bookingform' &&
		window.typenow !== 'ctx-attendeeform'
	)
		unregisterBlockType('events-manager/form-container');
});
