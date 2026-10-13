import { Button, CheckboxControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import SmallDataTable, {
	type SmallDataTableField,
} from '../../../../shared/small-data-table';

import { formatCentsAsPrice } from './price';
import { type BookingTicket, getDefaultCurrency } from './types';

type TicketTableProps = {
	tickets: BookingTicket[];
	onToggleActive: (ticketId: string, value: boolean) => void;
	onSelect: (index: number) => void;
	onDelete: (ticketId: string) => void;
	onDuplicate: (index: number) => void;
	onReorder: (tickets: BookingTicket[]) => void;
};

const TicketTable = ({
	tickets,
	onToggleActive,
	onSelect,
	onDelete,
	onDuplicate,
	onReorder,
}: TicketTableProps) => {
	const currency = getDefaultCurrency();
	const fields: SmallDataTableField<BookingTicket>[] = [
		{
			id: 'drag',
			ariaLabel: __('Drag to reorder', 'ctx-events'),
			headerStyle: { width: '2rem' },
			cellClassName: 'ctx-small-data-table__drag-handle',
			render: () => <span aria-hidden="true">⠿</span>,
		},
		{
			id: 'enabled',
			ariaLabel: __('Enabled', 'ctx-events'),
			headerStyle: { width: '2rem' },
			render: (ticket) => (
				<CheckboxControl
					label=""
					checked={Boolean(ticket.ticket_enabled)}
					onChange={(value) => onToggleActive(ticket.ticket_id, value)}
				/>
			),
		},
		{
			id: 'ticket',
			label: __('Ticket', 'ctx-events'),
			render: (ticket, index) => (
				<>
					<Button variant="link" onClick={() => onSelect(index)}>
						<b>{ticket.ticket_name}</b>
					</Button>
					<div className="row-actions">
						<Button
							variant="link"
							className="edit"
							onClick={() => onSelect(index)}
						>
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
				</>
			),
		},
		{
			id: 'description',
			label: __('Description', 'ctx-events'),
			render: (ticket) => ticket.ticket_description,
		},
		{
			id: 'price',
			label: __('Price', 'ctx-events'),
			render: (ticket) => formatCentsAsPrice(ticket.ticket_price, currency),
		},
		{
			id: 'spaces',
			label: __('Spaces', 'ctx-events'),
			render: (ticket) =>
				ticket.ticket_spaces === null || ticket.ticket_spaces === ''
					? '∞'
					: ticket.ticket_spaces,
		},
	];

	return (
		<SmallDataTable
			items={tickets}
			fields={fields}
			getRowKey={(ticket) => ticket.ticket_id}
			draggable
			onReorder={onReorder}
			className="wp-list-table widefat fixed striped table-view-list pages"
			emptyState={<p>{__('No tickets configured yet.', 'ctx-events')}</p>}
		/>
	);
};

export default TicketTable;
