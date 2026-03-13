import { formatPrice } from '@events/i18n';
import { Button } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { pencil, plus, trash } from '@wordpress/icons';
import type {
	AvailableTicketResource,
	BookingAttendeeResource,
	BookingDetail,
} from 'src/types/types';
import AttendeeEditModal from './AttendeeEditModal';

type Props = {
	booking: BookingDetail;
	onChange: (attendees: BookingAttendeeResource[]) => void;
};

const AttendeeSection = ({ booking, onChange }: Props) => {
	const [editingIndex, setEditingIndex] = useState<number | null>(null);
	const [isCreating, setIsCreating] = useState(false);

	const ticketById = (ticketId: string): AvailableTicketResource | undefined =>
		booking.availableTickets.find((ticket) => ticket.id === ticketId);

	const removeAttendee = (index: number) => {
		onChange(booking.attendees.filter((_, attendeeIndex) => attendeeIndex !== index));
	};

	const closeModal = () => {
		setEditingIndex(null);
		setIsCreating(false);
	};

	const handleCreate = (attendee: BookingAttendeeResource) => {
		onChange([...booking.attendees, attendee]);
		closeModal();
	};

	const handleUpdate = (attendee: BookingAttendeeResource) => {
		if (editingIndex === null) return;
		onChange(
			booking.attendees.map((current, index) =>
				index === editingIndex ? attendee : current,
			),
		);
		closeModal();
	};

	const activeAttendee =
		editingIndex === null ? null : booking.attendees[editingIndex] ?? null;

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
												currency: booking.price.finalPrice.currency,
											})
										: '—'}
								</td>
								<td className="booking-edit__attendee-actions">
									<Button
										icon={pencil}
										variant="tertiary"
										label={__('Edit', 'ctx-events')}
										onClick={() => setEditingIndex(index)}
									/>
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

			{booking.availableTickets.length > 0 && (
				<div className="booking-edit__add-attendee">
					<Button variant="secondary" icon={plus} onClick={() => setIsCreating(true)}>
						{__('Add Attendee', 'ctx-events')}
					</Button>
				</div>
			)}

			{(isCreating || editingIndex !== null) && (
				<AttendeeEditModal
					attendee={isCreating ? null : activeAttendee}
					booking={booking}
					onClose={closeModal}
					onSave={isCreating ? handleCreate : handleUpdate}
				/>
			)}
		</section>
	);
};

export default AttendeeSection;
