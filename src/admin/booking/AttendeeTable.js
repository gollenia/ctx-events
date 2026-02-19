import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import { formatPrice } from '@events/i18n';
import FullPrice from './FullPrice.js';

const AttendeeTable = ({ store }) => {
	const [state, dispatch] = store;
	const data = state.data;
	return (
		<>
			<div className="flex-header">
				<h2>{__('Attendees', 'ctx-events')}</h2>
				<Button
					onClick={() => {
						dispatch({ type: 'ADD_TICKET' });
					}}
					variant="secondary"
				>
					{__('Add Attendee', 'ctx-events')}
				</Button>
			</div>
			<table className="widefat">
				<thead>
					<tr>
						<th>{__('Name', 'ctx-events')}</th>
						{data.attendee_fields.map((field) => (
							<th>{field.label}</th>
						))}
						<th>{__('Price', 'ctx-events')}</th>
					</tr>
				</thead>
				<tbody>
					{data.attendees?.map((attendee, index) => {
						return (
							<tr className="alternate">
								<td>
									{data.available_tickets[attendee.ticket_id]?.name}
									<div class="row-actions">
										<span class="edit">
											<a
												onClick={() => {
													dispatch({
														type: 'SET_CURRENT_TICKET',
														payload: index,
													});
												}}
											>
												{__('Edit')}
											</a>{' '}
											|{' '}
										</span>

										<span class="trash">
											<a
												onClick={() => {
													dispatch({
														type: 'REMOVE_TICKET',
														payload: { index },
													});
												}}
												class="submitdelete"
												aria-label="„Kitchen Sink“ in den Papierkorb verschieben"
											>
												{__('Delete')}
											</a>
										</span>
									</div>
								</td>
								{data.attendee_fields.map((field) => (
									<td>{attendee.fields[field.fieldid]}</td>
								))}
								<td>
									{formatPrice(
										data.available_tickets[attendee.ticket_id]?.price,
										state.data.l10n.currency,
									)}
								</td>
							</tr>
						);
					})}
					<tr>
						<td>{__('Donation', 'ctx-events')}</td>
						<td colSpan={4}></td>

						<td>
							{formatPrice(
								state.data.booking.donation,
								state.data.l10n.currency,
							)}
						</td>
					</tr>
				</tbody>
				<tfoot>
					<FullPrice store={store} />
				</tfoot>
			</table>
		</>
	);
};

export default AttendeeTable;
