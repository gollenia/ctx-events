import { useEffect, useReducer } from 'react';

import useApiFetch from '@contexis/use-api-fetch';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import AttendeeTable from './AttendeeTable.js';
import BookingDetails from './BookingDetails.js';
import Log from './Log.js';
import Notes from './Notes.js';
import Payment from './Payment.js';
import Registration from './Registration.js';
import TicketModal from './TicketModal.js';
import initialState from './state/initialState.js';
import reducer from './state/reducer.js';
import saveBooking from './state/saveBooking.js';
import './style.scss';

const BookingEditor = ( { bookingId } ) => {
	const store = useReducer( reducer, initialState );
	const [ state, dispatch ] = store;
	const [ showNotesModal, setShowNotesModal ] = React.useState( false );

	const { data } = state;

	const { result, error } = useApiFetch( `/events/v2/booking/${ bookingId }` );

	useEffect( () => {
		if ( result ) {
			console.log( 'Booking data', result );
			dispatch( { type: 'SET_DATA', payload: result } );
			dispatch( { type: 'SET_STATE', payload: 'loaded' } );
		}
	}, [ result ] );

	console.log( 'BookingEditor state', state );

	useEffect( () => {
		if ( state.sendState === 'unsaved' ) {
			window.onbeforeunload = confirmExit;
		}
		function confirmExit() {
			return 'show warning';
		}
	}, [ state.sendState ] );

	if ( state.state === 'loading' || ! data ) {
		return <p>{ __( 'Loading Data...', 'events' ) }</p>;
	}

	if ( error ) {
		return <p __dangerouslySetInnerHTML={ { __html: error.message } }></p>;
	}

	return (
		<div>
			<BookingDetails store={ store } />
			<div className="booking-general">
				<Registration store={ store } />
				<div className="booking-history">
					<Log booking={ data.booking } />
					<Notes store={ store } />
				</div>
			</div>

			<AttendeeTable store={ store } />

			<Payment store={ store } />

			<div className="booking-actions">
				<Button
					onClick={ () => {
						saveBooking( bookingId, state, dispatch );
					} }
					variant={ state.sendState === 'unsaved' ? 'primary' : 'secondary' }
					className={ state.sendState === 'error' ? 'error' : '' }
				>
					{ __( 'Save' ) }
				</Button>
			</div>

			<TicketModal
				store={ store }
				onSave={ ( ticket, index ) => {
					dispatch( { type: 'SET_CURRENT_TICKET', payload: 999 } );
					dispatch( { type: 'SET_TICKET', payload: { ticket, index } } );
				} }
				onCancel={ () => {
					dispatch( { type: 'SET_CURRENT_TICKET', payload: 999 } );
				} }
			/>
		</div>
	);
};

export default BookingEditor;
