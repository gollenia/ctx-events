import { Button, SelectControl } from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';

import { PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import React from 'react';
import TicketModal from './ticketModal';

const MailSettings = ( props ) => {
	const { meta, setMeta } = props;
	const [ showMails, setShowMails ] = React.useState( false );
	const bookingFormList = useSelect( ( select ) => {
		const { getEntityRecords } = select( coreStore );
		const query = { per_page: -1 };
		const list = getEntityRecords( 'postType', 'gateway', query );

		let formsArray = [ { value: 0, label: '' } ];
		if ( ! list ) {
			return formsArray;
		}

		list.map( ( form ) => {
			formsArray.push( { value: form.id, label: form.title.raw } );
		} );

		return formsArray;
	}, [] );

	const attendeeFormList = useSelect( ( select ) => {
		const { getEntityRecords } = select( coreStore );
		const query = { per_page: -1 };
		const list = getEntityRecords( 'postType', 'attendeeform', query );

		let formsArray = [ { value: 0, label: '' } ];
		if ( ! list ) {
			return formsArray;
		}

		list.map( ( form ) => {
			formsArray.push( { value: form.id, label: form.title.raw } );
		} );

		return formsArray;
	}, [] );

	return (
		<PanelBody title={ __( 'Booking Forms', 'events' ) } initialOpen={ true } className="events-booking-settings">
			<SelectControl
				label={ __( 'Registration Form', 'events' ) }
				value={ meta._booking_form }
				onChange={ ( value ) => {
					setMeta( { _booking_form: value } );
				} }
				disabled={ ! meta._event_rsvp }
				options={ bookingFormList }
				disableCustomColors={ true }
			/>

			<SelectControl
				label={ __( 'Attendee Form', 'events' ) }
				value={ meta._attendee_form }
				onChange={ ( value ) => {
					setMeta( { _attendee_form: value } );
				} }
				disabled={ ! meta._event_rsvp }
				options={ attendeeFormList }
				disableCustomColors={ true }
			/>

			<Button
				onClick={ () => setShowTickets( ! showTickets ) }
				variant="secondary"
				disabled={ ! meta._event_rsvp }
			>
				{ __( 'Edit Tickets', 'events' ) }
			</Button>
			<TicketModal
				{ ...props }
				meta={ meta }
				setMeta={ setMeta }
				showTickets={ showTickets }
				setShowTickets={ setShowTickets }
			/>
		</PanelBody>
	);
};

export default MailSettings;
