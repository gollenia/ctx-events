import { __ } from '@wordpress/i18n';

import TicketRow from './TicketRow';
import type { BookingTicket } from './types';

type TicketTableProps = {
	tickets: BookingTicket[];
	onToggleActive: (ticketId: string, value: boolean) => void;
	onSelect: (index: number) => void;
	onDelete: (ticketId: string) => void;
	onDuplicate: (index: number) => void;
};

const TicketTable = ({
	tickets,
	onToggleActive,
	onSelect,
	onDelete,
	onDuplicate,
}: TicketTableProps) => {
	if (tickets.length === 0) {
		return <p>{__('No tickets configured yet.', 'ctx-events')}</p>;
	}

	return (
		<table className="wp-list-table widefat fixed striped table-view-list pages">
			<thead>
				<tr>
					<th style={{ width: '2rem' }} aria-label={__('Enabled', 'ctx-events')} />
					<th>{__('Ticket', 'ctx-events')}</th>
					<th>{__('Description', 'ctx-events')}</th>
					<th>{__('Price', 'ctx-events')}</th>
					<th>{__('Spaces', 'ctx-events')}</th>
				</tr>
			</thead>
			<tbody>
				{tickets.map((ticket, index) => (
					<TicketRow
						key={ticket.ticket_id}
						ticket={ticket}
						index={index}
						onToggleActive={onToggleActive}
						onSelect={onSelect}
						onDelete={onDelete}
						onDuplicate={onDuplicate}
					/>
				))}
			</tbody>
		</table>
	);
};

export default TicketTable;
