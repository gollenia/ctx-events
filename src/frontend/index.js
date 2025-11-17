import domReady from '@wordpress/dom-ready';
import initBooking from './booking/index.js';
import initUpcoming from './upcoming/index.js';

domReady(() => {
	initBooking();
	initUpcoming();
});
