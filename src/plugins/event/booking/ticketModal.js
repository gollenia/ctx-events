import { Button, Flex, FlexItem, Modal } from '@wordpress/components';
import { DataViews, filterSortAndPaginate } from '@wordpress/dataviews/wp';
import { __ } from '@wordpress/i18n';
import { trash } from '@wordpress/icons';
import { useMemo, useState } from 'react';
import { v4 as uuidv4 } from 'uuid';

import TicketEditor from './ticketEditor';
//import './booking.scss';

const TicketModal = ( props ) => {
	const { showTickets, setShowTickets, meta, setMeta } = props;
	const [ currentTicket, setCurrentTicket ] = useState( {} );
	const tickets = meta._event_tickets || [];
	const updateTickets = ( updatedTickets ) => {
		setMeta( { ...meta, _event_tickets: updatedTickets } );
	};

	const [ view, setView ] = useState( {
		type: 'table',
		search: '',
		page: 1,
		perPage: 100,
		fields: [ 'ticket_description', 'active', 'ticket_price' ],
		layout: {},
		filters: [],
		titleField: 'ticket_name',
		sort: {
			order: 'asc',
			orderby: 'ticket_order',
		},
	} );

	const fields = [
		{
			label: __( 'Name', 'events' ),
			id: 'ticket_name',
			enableHiding: false,
			enableGlobalSearch: true,
			type: 'string',
		},
		{
			label: __( 'Description', 'events' ),
			id: 'ticket_description',
			enableSorting: false,
			enableGlobalSearch: true,
			type: 'string',
		},
		{
			label: __( 'Price', 'events' ),
			id: 'ticket_price',
			render: ( { item } ) => {
				return item.ticket_price.toFixed( 2 );
			},

			enableSorting: false,
		},
		{
			label: __( 'Spaces', 'events' ),
			id: 'ticket_spaces',
			render: ( { item } ) => {
				return item.ticket_spaces;
			},

			enableSorting: false,
		},
	];

	const { data: shownData, paginationInfo } = useMemo( () => {
		return filterSortAndPaginate( tickets, view, fields );
	}, [ view, tickets ] );

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
							<DataViews
								data={ shownData }
								view={ view }
								onChangeView={ setView }
								paginationInfo={ paginationInfo }
								// You can define custom columns if needed
								defaultLayouts={ {
									table: {
										// Define default table layout settings
										spacing: 'normal',
										showHeader: true,
									},
								} }
								fields={ fields }
								actions={ [
									{
										id: 'toggle',
										label: ( [ item ] ) => {
											return item.active
												? __( 'Deactivate', 'events' )
												: __( 'Activate', 'events' );
										},
										isDestructive: true,
										icon: trash,
										callback: async ( [ item ] ) => {
											onToggle( item.slug );
										},
									},
									{
										id: 'settings',
										label: __( 'Settings', 'events' ),
										icon: trash,
										callback: async ( [ item ] ) => {
											setSlug( item.slug );
										},
									},
								] }
							>
								<DataViews.Layout />
							</DataViews>
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
