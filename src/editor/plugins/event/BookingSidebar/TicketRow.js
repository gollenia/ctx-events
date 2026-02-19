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
							
					}}
				/>
			</td>

			<td>
				<b><a onClick={() => onSelect(index)}>{ticket.ticket_name}</a>	</b>

				<div className="row-actions">
					<a className="edit" onClick={() => onSelect(index)}>
						{__('Edit', 'ctx-events')}
					</a>
					&nbsp;|&nbsp;
					<a className="view" onClick={() => onDuplicate(index)}>
						{__('Duplicate', 'ctx-events')}
					</a>
					&nbsp;|&nbsp;
					<span className="trash">
						<a onClick={() => onDelete(ticket.ticket_id)}>
							{__('Delete', 'ctx-events')}
						</a>
					</span>
				</div>
			</td>
			<td>{ticket.ticket_description}</td>
			<td>
				{formatPrice(ticket.ticket_price, window.eventEditorLocalization.currency)}
			</td>
			<td>{ticket.ticket_spaces}</td>
		</tr>
	);
};

export default TicketRow;
