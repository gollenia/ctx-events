import { Button, Flex, FlexItem, Modal } from '@wordpress/components';

import { useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { trash } from '@wordpress/icons';
import { v4 as uuidv4 } from 'uuid';

import TicketEditor from './TicketEditor';
import TicketTable from './TicketTable';

const TicketModal = (props) => {
	const { showTickets, setShowTickets, meta, setMeta } = props;
	const [currentTicket, setCurrentTicket] = useState({});
	const tickets = meta._event_tickets || [];
	const updateTickets = (updatedTickets) => {
		setMeta({ ...meta, _event_tickets: updatedTickets });
	};

	const [view, setView] = useState({
		type: 'table',
		search: '',
		page: 1,
		perPage: 100,
		fields: ['ticket_description', 'active', 'ticket_price'],
		layout: {},
		filters: [],
		titleField: 'ticket_name',
		sort: {
			order: 'asc',
			orderby: 'ticket_order',
		},
	});

	const fields = [
		{
			label: __('Name', 'ctx-events'),
			id: 'ticket_name',
			enableHiding: false,
			enableGlobalSearch: true,
			type: 'string',
		},
		{
			label: __('Description', 'ctx-events'),
			id: 'ticket_description',
			enableSorting: false,
			enableGlobalSearch: true,
			type: 'string',
		},
		{
			label: __('Price', 'ctx-events'),
			id: 'ticket_price',
			render: ({ item }) => {
				return item.ticket_price;
			},

			enableSorting: false,
		},
		{
			label: __('Spaces', 'ctx-events'),
			id: 'ticket_spaces',
			render: ({ item }) => {
				return item.ticket_spaces;
			},

			enableSorting: false,
		},
	];

	const closeModal = () => {
		setShowTickets(false);
	};

	const onDelete = (ticket_id) => {
		const newTickets = tickets.filter((t) => t.ticket_id !== ticket_id);
		updateTickets(newTickets);
	};

	const onUpdate = () => {
		const newTickets = [...tickets];
		const index = newTickets.findIndex(
			(t) => t.ticket_id === currentTicket.ticket_id,
		);
		if (index > -1) {
			newTickets[index] = currentTicket;
		} else {
			newTickets.push(currentTicket);
		}
		updateTickets(newTickets);
		setCurrentTicket({});
	};

	const onToggleActive = (ticket_id, value) => {
		updateTickets(
			tickets.map((ticket) => {
				if (ticket.ticket_id === ticket_id) {
					return { ...ticket, ticket_enabled: value };
				}
				return ticket;
			}),
		);
	};

	const createNewTicket = () => ({
		ticket_id: uuidv4(),
		ticket_name: __('Default Ticket', 'ctx-events'),
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
	});

	const modalTitle =
		Object.keys(currentTicket).length > 0
			? currentTicket.ticket_id === 0
				? __('New Ticket', 'ctx-events')
				: __('Edit Ticket', 'ctx-events')
			: __('Tickets', 'ctx-events');

	return (
		<>
			{showTickets && (
				<Modal title={modalTitle} onRequestClose={closeModal} size="large">
					{Object.keys(currentTicket).length > 0 ? (
						<TicketEditor
							ticket={currentTicket}
							setTicket={setCurrentTicket}
							onUpdate={onUpdate}
							onCancel={() => {
								setCurrentTicket({});
							}}
						/>
					) : (
						<>
							<TicketTable
								tickets={tickets}
								onToggleActive={onToggleActive}
								onSelect={(index) => setCurrentTicket(tickets[index])}
								onDelete={onDelete}
								onDuplicate={(index) => {
									const ticketToDuplicate = tickets[index];
									const newTicket = {
										...ticketToDuplicate,
										ticket_id: uuidv4(),
										ticket_name: `${ticketToDuplicate.ticket_name} (Copy)`,
										ticket_order: tickets.length + 1,
									};
									updateTickets([...tickets, newTicket]);
								}}
							/>
							<Flex justify="flex-end" style={{ marginTop: '1rem' }}>
								<FlexItem>
									<Button
										variant="primary"
										onClick={() => {
											setCurrentTicket(createNewTicket());
										}}
									>
										{__('Add Ticket', 'ctx-events')}
									</Button>
								</FlexItem>
							</Flex>
						</>
					)}
				</Modal>
			)}
		</>
	);
};

export default TicketModal;
