import { formatPrice } from '@events/i18n';
import { Button, Panel, PanelBody } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __, _x } from '@wordpress/i18n';
import { plus } from '@wordpress/icons';
import type {
	AvailableTicketResource,
	BookingAttendeeResource,
	BookingDetail,
} from 'src/types/types';
import CancelBookingFromAttendeeModal from './CancelBookingFromAttendeeModal';
import CancelAttendeeModal from './CancelAttendeeModal';
import AttendeeEditModal from './AttendeeEditModal';

type Props = {
	booking: BookingDetail;
	onChange: (attendees: BookingAttendeeResource[]) => void;
	onCancelAttendee: (
		attendee: BookingAttendeeResource,
		options: { sendMail: boolean; cancellationAmountCents: number },
	) => Promise<void>;
	onCancelBooking: (options: { sendMail: boolean }) => Promise<void>;
};

const AttendeeSection = ({
	booking,
	onChange,
	onCancelAttendee,
	onCancelBooking,
}: Props) => {
	const [editingIndex, setEditingIndex] = useState<number | null>(null);
	const [isCreating, setIsCreating] = useState(false);
	const [cancellingIndex, setCancellingIndex] = useState<number | null>(null);
	const [isBookingCancelOpen, setIsBookingCancelOpen] = useState(false);
	const discountAmountCents = booking.price.discountAmount.amountCents ?? 0;
	const currency = booking.price.finalPrice.currency;
	const activeAttendeeCount = booking.attendees.filter(
		(attendee) => attendee.status === 'active',
	).length;

	const ticketById = (ticketId: string): AvailableTicketResource | undefined =>
		booking.availableTickets.find((ticket) => ticket.id === ticketId);

	const removeAttendee = (index: number) => {
		onChange(
			booking.attendees.filter((_, attendeeIndex) => attendeeIndex !== index),
		);
	};

	const closeModal = () => {
		setEditingIndex(null);
		setIsCreating(false);
		setCancellingIndex(null);
		setIsBookingCancelOpen(false);
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
		editingIndex === null ? null : (booking.attendees[editingIndex] ?? null);
	const cancellingAttendee =
		cancellingIndex === null ? null : (booking.attendees[cancellingIndex] ?? null);

	return (
		<Panel header={__('Attendees', 'ctx-events')}>
			<PanelBody>
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
							const isPersisted = attendee.id !== null;
							const isActive = attendee.status === 'active';
							const canCancel = isPersisted && isActive;
							const statusLabel =
								attendee.status === 'cancelled'
									? _x('Cancelled', 'booking attendee status', 'ctx-events')
									: attendee.status === 'checked_in'
										? _x('Checked in', 'booking attendee status', 'ctx-events')
										: null;

							return (
								<tr key={index}>
									<td>{ticket?.name ?? attendee.ticketId}</td>
									<td>
										{fullName}
									</td>
									<td>{formatPrice(attendee.ticketPrice)}</td>
									<td className="booking-edit__attendee-actions">
										{statusLabel ? (
											<span className="booking-edit__attendee-status">
												{statusLabel}
											</span>
										) : (
											<>
												<Button
													variant="link"
													onClick={() => setEditingIndex(index)}
													disabled={!isActive}
												>
													{__('Edit', 'ctx-events')}
												</Button>
												{isPersisted ? (
												<Button
													variant="link"
													isDestructive
													onClick={() => {
														if (activeAttendeeCount <= 1) {
															setIsBookingCancelOpen(true);
															return;
														}

														setCancellingIndex(index);
													}}
													disabled={!canCancel}
												>
													{_x('Cancel attendee', 'booking action button', 'ctx-events')}
													</Button>
												) : (
													<Button
														variant="link"
														isDestructive
														onClick={() => removeAttendee(index)}
													>
														{__('Remove', 'ctx-events')}
													</Button>
												)}
											</>
										)}
									</td>
								</tr>
							);
						})}
					</tbody>
					<tfoot>
						<tr className="booking-edit__attendees-summary">
							<th colSpan={2}>{__('Booking price', 'ctx-events')}</th>
							<td>{formatPrice(booking.price.bookingPrice)}</td>
							<td></td>
						</tr>
						<tr className="booking-edit__attendees-summary">
							<th colSpan={2}>{__('Donation', 'ctx-events')}</th>
							<td>{formatPrice(booking.price.donationAmount)}</td>
							<td></td>
						</tr>
						<tr className="booking-edit__attendees-summary">
							<th colSpan={2}>{__('Coupon / Discount', 'ctx-events')}</th>
							<td>
								-{formatPrice({ amountCents: discountAmountCents, currency })}
							</td>
							<td></td>
						</tr>
						<tr className="booking-edit__attendees-summary booking-edit__attendees-summary--total">
							<th colSpan={2}>{__('Final price', 'ctx-events')}</th>
							<td>{formatPrice(booking.price.finalPrice)}</td>
							<td></td>
						</tr>
					</tfoot>
				</table>

				{booking.availableTickets.length > 0 && (
					<div className="booking-edit__add-attendee">
						<Button
							variant="secondary"
							icon={plus}
							onClick={() => setIsCreating(true)}
						>
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

				{cancellingAttendee && cancellingAttendee.id !== null ? (
					<CancelAttendeeModal
						attendee={cancellingAttendee}
						onClose={closeModal}
						onConfirm={(options) =>
							onCancelAttendee(cancellingAttendee, options)
						}
					/>
				) : null}

				{isBookingCancelOpen ? (
					<CancelBookingFromAttendeeModal
						onClose={closeModal}
						onConfirm={onCancelBooking}
					/>
				) : null}
			</PanelBody>
		</Panel>
	);
};

export default AttendeeSection;
