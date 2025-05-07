import { STATES } from './constants';

const sendOrder = ( state, dispatch ) => {
	const controller = new AbortController();
	const signal = controller.signal;

	const { request, event, response, modal } = state;
	dispatch( { type: 'SET_ORDER_STATE', payload: STATES.LOADING } );

	setTimeout( () => {
		if ( modal.orderState == STATES.IDLE ) return;
		dispatch( { type: 'SET_ORDER_STATE', payload: STATES.DELAY } );
	}, 3000 );

	setTimeout( () => {
		if ( modal.orderState == STATES.IDLE ) return;
		dispatch( { type: 'SET_ORDER_STATE', payload: STATES.HUGE_DELAY } );
	}, 7000 );

	setTimeout( () => {
		if ( modal.orderState == STATES.IDLE ) return;
		dispatch( { type: 'SET_ORDER_STATE', payload: STATES.ERROR } );
		controller.abort();
	}, 10000 );

	let fetchRequest = {
		...request,
		_wpnonce: event._nonce,
		event_id: event.id,
		attendees: {},
	};

	for ( const id of Object.keys( event.tickets_available ) ) {
		fetchRequest[ 'attendees' ][ id ] = [];
	}

	request.tickets.map( ( ticket ) => {
		fetchRequest.attendees[ ticket.id ].push( ticket.fields );
	} );

	fetch( `/wp-json/events/v2/booking/${ event.id }`, {
		method: 'POST',
		body: JSON.stringify( fetchRequest ),
		headers: new Headers( {
			'Content-Type': 'application/json;charset=UTF-8',
		} ),
		beforeSend: function ( xhr ) {
			xhr.setRequestHeader( 'X-WP-Nonce', event._nonce );
		},
	} )
		.then( ( resp ) => resp.json() )
		.then( ( response ) => {
			console.log( response );
			dispatch( { type: 'BOOKING_RESPONSE', payload: { state: STATES.SUCCESS, response } } );
			if ( response.gateway.url ) {
				window.location.replace( response.gateway.url );
			}
		} )
		.catch( ( error ) => {
			console.log( error );
			dispatch( {
				type: 'BOOKING_RESPONSE',
				payload: { state: STATES.ERROR, response: { result: false, message: error } },
			} );
		} );
};

export default sendOrder;
