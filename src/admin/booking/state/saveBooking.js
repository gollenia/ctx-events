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

	dispatch( { type: 'SET_SEND_STATE', payload: 'saving' } );
	console.log( 'Saving booking with data:', data.booking );
	apiFetch( {
		path: `/events/v2/booking/${ bookingId }`,
		method: 'PUT',
		data: data.booking,
	} )
		.then( ( apiResponse ) => {
			console.log( 'Booking saved successfully', apiResponse );
			dispatch( { type: 'SET_SEND_STATE', payload: 'saved' } );
		} )
		.catch( ( error ) => {
			console.error( 'Error saving booking:', error );
			dispatch( { type: 'SET_SEND_STATE', payload: 'failed' } );
		} );
};

export default saveBooking;
