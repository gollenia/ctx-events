import { CheckboxControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { formatPrice } from '@events/i18n';

const TicketRow = (props) => {
	const { ticket, index, onDelete, onToggleActive, onSelect, onDuplicate } =
		props;

	return (
		<tr>
			<td>
				<CheckboxControl
					label=""
					checked={ticket.ticket_enabled == 1}
					onChange={(value) => {
						//ticket.ticket_enabled = value ? 1 : 0;
						onToggleActive(ticket.ticket_id, value ? 1 : 0);
					}}
				/>
			</td>

			<td>
				<b>{ticket.ticket_name}</b>

				<div className="row-actions">
					<a className="edit" onClick={() => onSelect(index)}>
						{__('Edit', 'events')}
					</a>
					&nbsp;|&nbsp;
					<a className="view" onClick={() => onDuplicate(index)}>
						{__('Duplicate', 'events')}
					</a>
					&nbsp;|&nbsp;
					<span className="trash">
						<a onClick={() => onDelete(ticket.ticket_id)}>
							{__('Delete', 'events')}
						</a>
					</span>
				</div>
			</td>
			<td>{ticket.ticket_description}</td>
			<td>
				{formatPrice(ticket.ticket_price, eventBlocksLocalization.currency)}
			</td>
			<td>{ticket.ticket_spaces}</td>
			<td>{ticket.ticket_min}</td>
			<td>{ticket.ticket_max}</td>
		</tr>
	);
};

export default TicketRow;
