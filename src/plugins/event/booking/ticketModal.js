import { Button, Flex, FlexItem, Modal } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import { v4 as uuidv4 } from 'uuid';

import TicketEditor from './ticketEditor';
import TicketRow from './ticketRow';
//import './booking.scss';

const TicketModal = ( props ) => {
	const { showTickets, setShowTickets, meta, setMeta } = props;
	const [ currentTicket, setCurrentTicket ] = useState( {} );
	const tickets = meta._event_tickets || [];
	const updateTickets = ( updatedTickets ) => {
		setMeta( { ...meta, _event_tickets: updatedTickets } );
	};

	console.log( 'tickets', tickets );

	const closeModal = () => {
		setShowTickets( false );
	};

	const onDelete = ( ticket_id ) => {
		const newTickets = tickets.filter( ( t ) => t.ticket_id !== ticket_id );
		updateTickets( newTickets );
	};

	const onUpdate = () => {
		const newTickets = [ ...tickets ];
		const index = newTickets.findIndex( ( t ) => t.ticket_id === currentTicket.ticket_id );
		if ( index > -1 ) {
			newTickets[ index ] = currentTicket;
		} else {
			newTickets.push( currentTicket );
		}
		updateTickets( newTickets );
		setCurrentTicket( {} );
	};

	const onToggleActive = ( ticket_id, value ) => {
		updateTickets(
			tickets.map( ( ticket ) => {
				if ( ticket.ticket_id === ticket_id ) {
					return { ...ticket, ticket_enabled: value };
				}
				return ticket;
			} )
		);
	};

	const createNewTicket = () => ( {
		ticket_id: uuidv4(),
		ticket_name: __( 'Default Ticket', 'events' ),
		ticket_description: '',
		ticket_price: 0,
		ticket_spaces: meta._event_spaces || 0,
		ticket_min: 0,
		ticket_max: 0,
		ticket_start: '',
		ticket_end: '',
		ticket_enabled: 1,
		ticket_order: tickets.length + 1,
		ticket_form: 0,
	} );

	const modalTitle =
		Object.keys( currentTicket ).length > 0
			? currentTicket.ticket_id == 0
				? __( 'New Ticket', 'events' )
				: __( 'Edit Ticket', 'events' )
			: __( 'Tickets', 'events' );

	return (
		<>
			{ showTickets && (
				<Modal title={ modalTitle } onRequestClose={ closeModal } size="large">
					{ Object.keys( currentTicket ).length > 0 ? (
						<TicketEditor
							ticket={ currentTicket }
							setTicket={ setCurrentTicket }
							onUpdate={ onUpdate }
							onCancel={ () => {
								setCurrentTicket( {} );
							} }
						/>
					) : (
						<>
							<table className="wp-list-table widefat striped table-view-list posts">
								<thead>
									<tr>
										<th>{ __( 'Active', 'events' ) }</th>
										<th>{ __( 'Name', 'events' ) }</th>
										<th>{ __( 'Description', 'events' ) }</th>
										<th>{ __( 'Price', 'events' ) }</th>
										<th>{ __( 'Spaces', 'events' ) }</th>
										<th>{ __( 'Min', 'events' ) }</th>
										<th>{ __( 'Max', 'events' ) }</th>
									</tr>
								</thead>
								<tbody>
									{ tickets.length === 0 ? (
										<tr>
											<td colSpan="7">{ __( 'No tickets found.', 'events' ) }</td>
										</tr>
									) : (
										<>
											{ tickets.map( ( ticket, index ) => (
												<TicketRow
													ticket={ ticket }
													index={ index }
													onDelete={ onDelete }
													onSelect={ ( index ) => {
														setCurrentTicket( tickets[ index ] );
													} }
													onToggleActive={ onToggleActive }
													onDuplicate={ ( index ) => {
														const newTicket = { ...tickets[ index ] };
														newTicket.ticket_id = uuidv4();
														newTicket.is_new = true;
														setCurrentTicket( newTicket );
													} }
												/>
											) ) }
										</>
									) }
								</tbody>
							</table>
							<Flex justify="flex-end" style={ { marginTop: '1rem' } }>
								<FlexItem>
									<Button
										variant="primary"
										onClick={ () => {
											setCurrentTicket( createNewTicket() );
										} }
									>
										{ __( 'Add Ticket', 'events' ) }
									</Button>
								</FlexItem>
							</Flex>
						</>
					) }
				</Modal>
			) }
		</>
	);
};

export default TicketModal;
