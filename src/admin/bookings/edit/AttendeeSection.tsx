import { formatPrice } from '@events/i18n';
import { Button, SelectControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { plus, trash } from '@wordpress/icons';
import type { AvailableTicketResource, BookingAttendeeResource, BookingDetail } from 'src/types/types';

type Props = {
	booking: BookingDetail;
	onChange: (attendees: BookingAttendeeResource[]) => void;
};

const AttendeeSection = ({ booking, onChange }: Props) => {
	const [selectedTicketId, setSelectedTicketId] = useState<string>(
		booking.availableTickets[0]?.id ?? '',
	);

	const ticketById = (ticketId: string): AvailableTicketResource | undefined =>
		booking.availableTickets.find((ticket) => ticket.id === ticketId);

	const addAttendee = () => {
		if (!selectedTicketId) return;

		const newAttendee: BookingAttendeeResource = {
			ticketId: selectedTicketId,
			name: null,
			metadata: {},
		};

		onChange([...booking.attendees, newAttendee]);
	};

	const removeAttendee = (index: number) => {
		onChange(booking.attendees.filter((_, attendeeIndex) => attendeeIndex !== index));
	};

	const ticketOptions = booking.availableTickets.map((ticket) => ({
		value: ticket.id,
		label: `${ticket.name} (${formatPrice({ amountCents: ticket.price, currency: booking.price.currency })})`,
	}));

	return (
		<section className="booking-edit__section">
			<h3>{__('Attendees', 'ctx-events')}</h3>

			<table className="widefat booking-edit__attendees">
				<thead>
					<tr>
						<th>{__('Ticket', 'ctx-events')}</th>
						<th>{__('Name', 'ctx-events')}</th>
						<th>{__('Price', 'ctx-events')}</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{booking.attendees.length === 0 && (
						<tr>
							<td colSpan={4} style={{ textAlign: 'center', color: '#888' }}>
								{__('No attendees.', 'ctx-events')}
							</td>
						</tr>
					)}
					{booking.attendees.map((attendee, index) => {
						const ticket = ticketById(attendee.ticketId);
						const fullName = attendee.name
							? `${attendee.name.firstName} ${attendee.name.lastName}`.trim()
							: '—';

						return (
							<tr key={index}>
								<td>{ticket?.name ?? attendee.ticketId}</td>
								<td>{fullName}</td>
								<td>
									{ticket
										? formatPrice({
												amountCents: ticket.price,
												currency: booking.price.currency,
											})
										: '—'}
								</td>
								<td>
									<Button
										icon={trash}
										variant="tertiary"
										isDestructive
										label={__('Remove', 'ctx-events')}
										onClick={() => removeAttendee(index)}
									/>
								</td>
							</tr>
						);
					})}
				</tbody>
			</table>

			{ticketOptions.length > 0 && (
				<div className="booking-edit__add-attendee">
					<SelectControl
						label={__('Ticket', 'ctx-events')}
						value={selectedTicketId}
						options={ticketOptions}
						onChange={setSelectedTicketId}
					/>
					<Button variant="secondary" icon={plus} onClick={addAttendee}>
						{__('Add Attendee', 'ctx-events')}
					</Button>
				</div>
			)}
		</section>
	);
};

export default AttendeeSection;
