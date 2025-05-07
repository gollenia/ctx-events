import apiFetch from '@wordpress/api-fetch';

const saveBooking = ( bookingId, state, dispatch ) => {
	const data = state.data;

	let fetchRequest = {
		registration: data.registration,
		booking_id: bookingId,
		gateway: data.booking.gateway,
		donation: data.booking.donation,
		event_id: data.event.event_id,
		note: data.booking.note,
		attendees: {},
		coupon: data.booking.coupon,
	};

	for ( const id of Object.keys( data.tickets_available ) ) {
		fetchRequest[ 'attendees' ][ id ] = [];
	}

	Object.values( data.attendees ).map( ( ticket ) => {
		fetchRequest.attendees[ ticket.ticket_id ].push( ticket.fields );
	} );

	dispatch( { type: 'SET_SEND_STATE', payload: 'saving' } );

	apiFetch( {
		path: `/events/v2/booking/${ bookingId }`,
		method: 'PUT',
		data: fetchRequest,
	} )
		.then( ( apiResponse ) => {
			dispatch( { type: 'SET_SEND_STATE', payload: 'saved' } );
		} )
		.catch( ( error ) => {
			dispatch( { type: 'SET_SEND_STATE', payload: 'failed' } );
		} );
};

export default saveBooking;
