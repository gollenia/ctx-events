import { Button, Flex, FlexItem, Modal } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { v4 as uuidv4 } from 'uuid';

import TicketEditor from './TicketEditor';
import TicketTable from './TicketTable';
import type { BookingSidebarProps, BookingTicket } from './types';

type TicketModalProps = BookingSidebarProps & {
	showTickets: boolean;
	setShowTickets: (value: boolean) => void;
};

const emptyTicket = (tickets: BookingTicket[], fallbackSpaces: string | number): BookingTicket => ({
	ticket_id: uuidv4(),
	ticket_name: __('Default Ticket', 'ctx-events'),
	ticket_description: '',
	ticket_price: 0,
	ticket_spaces: fallbackSpaces || 0,
	ticket_min: 0,
	ticket_max: 0,
	ticket_start: '',
	ticket_end: '',
	ticket_enabled: true,
	ticket_order: tickets.length + 1,
	ticket_form: 0,
});

const TicketModal = ({
	meta,
	updateMeta,
	showTickets,
	setShowTickets,
}: TicketModalProps) => {
	const [currentTicket, setCurrentTicket] = useState<BookingTicket | null>(null);
	const tickets = meta._event_tickets ?? [];

	const updateTickets = (updatedTickets: BookingTicket[]) => {
		updateMeta({ _event_tickets: updatedTickets });
	};

	const onDelete = (ticketId: string) => {
		updateTickets(tickets.filter((ticket) => ticket.ticket_id !== ticketId));
	};

	const onUpdate = () => {
		if (!currentTicket) {
			return;
		}

		const index = tickets.findIndex(
			(ticket) => ticket.ticket_id === currentTicket.ticket_id,
		);

		if (index === -1) {
			updateTickets([...tickets, currentTicket]);
		} else {
			updateTickets(
				tickets.map((ticket, ticketIndex) =>
					ticketIndex === index ? currentTicket : ticket,
				),
			);
		}

		setCurrentTicket(null);
	};

	const onToggleActive = (ticketId: string, value: boolean) => {
		updateTickets(
			tickets.map((ticket) =>
				ticket.ticket_id === ticketId
					? { ...ticket, ticket_enabled: value }
					: ticket,
			),
		);
	};

	const duplicateTicket = (index: number) => {
		const ticketToDuplicate = tickets[index];
		if (!ticketToDuplicate) {
			return;
		}

		updateTickets([
			...tickets,
			{
				...ticketToDuplicate,
				ticket_id: uuidv4(),
				ticket_name: `${ticketToDuplicate.ticket_name} (Copy)`,
				ticket_order: tickets.length + 1,
			},
		]);
	};

	if (!showTickets) {
		return null;
	}

	return (
		<Modal
			title={
				currentTicket
					? __('Edit Ticket', 'ctx-events')
					: __('Tickets', 'ctx-events')
			}
			onRequestClose={() => setShowTickets(false)}
			size="large"
		>
			{currentTicket ? (
				<TicketEditor
					ticket={currentTicket}
					setTicket={setCurrentTicket}
					onUpdate={onUpdate}
					onCancel={() => setCurrentTicket(null)}
				/>
			) : (
				<>
					<TicketTable
						tickets={tickets}
						onToggleActive={onToggleActive}
						onSelect={(index) => setCurrentTicket(tickets[index] ?? null)}
						onDelete={onDelete}
						onDuplicate={duplicateTicket}
					/>
					<Flex justify="flex-end" style={{ marginTop: '1rem' }}>
						<FlexItem>
							<Button
								variant="primary"
								onClick={() =>
									setCurrentTicket(
										emptyTicket(tickets, meta._booking_capacity ?? 0),
									)
								}
							>
								{__('Add Ticket', 'ctx-events')}
							</Button>
						</FlexItem>
					</Flex>
				</>
			)}
		</Modal>
	);
};

export default TicketModal;
