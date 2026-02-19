import TicketRow from "./TicketRow";
import { __ } from '@wordpress/i18n';

function TicketTable({ tickets, onToggleActive, onSelect, onDelete, onDuplicate }) {
	return (
		<table className="wp-list-table widefat fixed striped table-view-list pages">
			<thead>
				<tr>
					<th style={{width: '2rem'}}></th>
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
}

export default TicketTable;