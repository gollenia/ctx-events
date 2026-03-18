import { Button, CheckboxControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import { formatPrice } from '@events/i18n';

import { getEventEditorLocalization, type BookingTicket } from './types';

type TicketRowProps = {
	ticket: BookingTicket;
	index: number;
	onDelete: (ticketId: string) => void;
	onToggleActive: (ticketId: string, value: boolean) => void;
	onSelect: (index: number) => void;
	onDuplicate: (index: number) => void;
};

const TicketRow = ({
	ticket,
	index,
	onDelete,
	onToggleActive,
	onSelect,
	onDuplicate,
}: TicketRowProps) => {
	const currency = getEventEditorLocalization().currency ?? 'USD';

	return (
		<tr>
			<td>
				<CheckboxControl
					label=""
					checked={Boolean(ticket.ticket_enabled)}
					onChange={(value) => {
						onToggleActive(ticket.ticket_id, value);
					}}
				/>
			</td>

			<td>
				<Button variant="link" onClick={() => onSelect(index)}>
					<b>{ticket.ticket_name}</b>
				</Button>

				<div className="row-actions">
					<Button variant="link" className="edit" onClick={() => onSelect(index)}>
						{__('Edit', 'ctx-events')}
					</Button>
					{' | '}
					<Button
						variant="link"
						className="view"
						onClick={() => onDuplicate(index)}
					>
						{__('Duplicate', 'ctx-events')}
					</Button>
					{' | '}
					<Button
						variant="link"
						className="trash"
						onClick={() => onDelete(ticket.ticket_id)}
					>
						{__('Delete', 'ctx-events')}
					</Button>
				</div>
			</td>
			<td>{ticket.ticket_description}</td>
			<td>{formatPrice(ticket.ticket_price, currency)}</td>
			<td>{ticket.ticket_spaces}</td>
		</tr>
	);
};

export default TicketRow;
