import { __ } from '@wordpress/i18n';

const initializer = ( initialState ) => initialState;

const reducer = ( state = {}, action ) => {
	const { type, payload } = action;
	const { event } = state;
	switch ( type ) {
		case 'SET_EVENT':
			state.event = payload;
			state.wizard.step = payload?.forms?.attendee_fields?.length === 0 ? 1 : 0;
			return { ...state };

		case 'SET_WIZARD':
			state.wizard.step = payload;
			state.wizard.checkValidityToken = Date.now();
			return { ...state };

		case 'INCREMENT_WIZARD':
			state.wizard.step = state.wizard.step + 1;
			state.wizard.checkValidityToken = Date.now();
			return { ...state };

		case 'DECREMENT_WIZARD':
			state.wizard.step = state.wizard.step - 1;
			state.wizard.checkValidityToken = Date.now();
			return { ...state };

		case 'SET_MODAL':
			state.modal.visible = payload;
			state.modal.title = payload
				? `${ __( 'Registration', 'events' ) } ${ event?.title }`
				: state.originalDocumentTitle;
			return { ...state };

		case 'SET_INIT_STATE':
			state.modal.initState = payload;
			return { ...state };

		case 'ADD_ATTENDEE':
			const attendee = {
				ticket_id: payload,
				fields: {},
			};

			state.request.attendees.push( attendee );
			state.wizard.checkValidityToken = Date.now();
			return { ...state };

		case 'REMOVE_ATTENDEE':
			const index =
				payload.index !== undefined
					? payload.index
					: state.request.attendees.findIndex( ( attendee ) => attendee.ticket_id === payload.id );
			state.request.attendees = state.request.attendees.filter( ( _, i ) => i !== index );
			state.wizard.checkValidityToken = Date.now();
			return { ...state };

		case 'SET_FIELD':
			if ( payload.form === 'attendee' ) {
				state.request.attendees[ payload.index ].fields[ payload.field ] = payload.value;
			}
			if ( payload.form === 'registration' ) {
				state.request.registration[ payload.field ] = payload.value;
			}
			if ( payload.form === 'donation' ) {
				state.request.donation = payload.value;
			}
			state.wizard.checkValidityToken = Date.now();
			return { ...state };

		case 'SET_COUPON':
			state.request.coupon = payload;
			return { ...state };

		case 'SET_COUPON_LOADING':
			state.modal.couponButton = payload;
			return { ...state };

		case 'COUPON_RESPONSE':
			state.response.coupon = payload;
			return { ...state };

		case 'BOOKING_RESPONSE':
			state.response.booking = payload.response;
			state.modal.orderState = payload.state;
			state.wizard.step = state.wizard.step + 1;
			return { ...state };

		case 'SET_GATEWAY':
			state.request.gateway = payload;
			state.wizard.checkValidityToken = Date.now();
			return { ...state };

		case 'VALIDITY':
			for ( let key in payload ) {
				state.wizard.steps[ key ].valid = payload[ key ];
			}

			return { ...state };

		case 'RESET':
			return initializer();

		default:
	}

	return { ...state };
};
export default reducer;
