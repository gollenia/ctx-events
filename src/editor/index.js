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
import * as booking from './blocks/booking/index.js';

/**
 * Form dependencies.
 */
import * as couponEditor from './blocks/coupon-editor/index.js';
import * as details from './blocks/details/index.js';
import * as detailsAudience from './blocks/details-audience/index.js';
import * as detailsDate from './blocks/details-date/index.js';
import * as detailsItem from './blocks/details-item/index.js';
import * as detailsLocation from './blocks/details-location/index.js';
import * as detailsPerson from './blocks/details-person/index.js';
import * as detailsPrice from './blocks/details-price/index.js';
import * as detailsShutdown from './blocks/details-shutdown/index.js';
import * as detailsSpaces from './blocks/details-spaces/index.js';
import * as detailsTime from './blocks/details-time/index.js';
import * as featured from './blocks/featured/index.js';
import * as formCheckbox from './blocks/form/checkbox/index.js';
import * as formContainer from './blocks/form/container/index.js';
import * as formCountry from './blocks/form/country/index.js';
import * as formDate from './blocks/form/date/index.js';
import * as formEmail from './blocks/form/email/index.js';
import * as formHTML from './blocks/form/html/index.js';
import * as formPhone from './blocks/form/phone/index.js';
import * as formRadio from './blocks/form/radio/index.js';
import * as formSelect from './blocks/form/select/index.js';
import * as formText from './blocks/form/text/index.js';
import * as formTextarea from './blocks/form/textarea/index.js';
import * as locationEditor from './blocks/location-editor/index.js';
import * as personEditor from './blocks/person-editor/index.js';
import * as upcoming from './blocks/upcoming/index.js';

/**
 * Plugin dependencies.
 */
import BookingStatus from './plugins/event/BookingStatus.js';
import bookingSidebar from './plugins/event/BookingSidebar.js';
import datetimeSelector from './plugins/event/datetime.js';
import locationSelector from './plugins/event/location.js';
import peopleSelector from './plugins/event/people.js';
import recurrenceSettings from './plugins/event/recurrence.js';
import LocationAddress from './plugins/location/LocationAddress.js';
import personalData from './plugins/person/personal.js';
import DashboardButton from './plugins/event/DashboardButton.js';

const blocks = [
	upcoming,
	booking,
	featured,
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
