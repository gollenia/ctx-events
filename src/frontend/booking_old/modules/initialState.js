import { __ } from '@wordpress/i18n';
import { STATES } from './constants';

const initialState = {
	modal: {
		visible: window.location.hash.indexOf('booking') != -1 ? true : false,
		title: document.title,
		originalDocumentTitle: document.title,
		orderState: STATES.IDLE,
		initState: STATES.IDLE,
	},
	wizard: {
		steps: {
			tickets: {
				enabled: true,
				step: 0,
				label: __('Tickets', 'ctx-events'),
				valid: false,
				isLast: false,
			},
			registration: {
				enabled: true,
				step: 1,
				label: __('Registration', 'ctx-events'),
				valid: false,
				isLast: false,
			},
			payment: {
				enabled: true,
				step: 2,
				label: __('Payment', 'ctx-events'),
				valid: false,
				isLast: false,
			},
			success: {
				enabled: true,
				step: 3,
				label: __('Done', 'ctx-events'),
				valid: false,
				isLast: true,
			},
		},
		keys: ['tickets', 'registration', 'payment', 'success'],
		step: 0,
		checkValidityToken: Date.now(),
	},
	response: {
		booking: {
			booking_id: 0,
		},
		error: '',
		data: {},
		coupon: {
			code: '',
		},
	},
	request: {
		event_id: 0,
		attendees: [],
		registration: {},
		donation: 0.0,
		gateway: 'offline',
		coupon: '',
	},
	event: false,
};

export default initialState;
