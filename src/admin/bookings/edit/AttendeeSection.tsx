import { formatPrice } from '@events/i18n';
import { Button, Panel, PanelBody } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __, _x } from '@wordpress/i18n';
import { Icon, pencil, plus, trash } from '@wordpress/icons';
import type {
	AvailableTicketResource,
	BookingAttendeeResource,
	BookingDetail,
} from 'src/types/types';
import AttendeeEditModal from './AttendeeEditModal';
import CancelAttendeeModal from './CancelAttendeeModal';
import CancelBookingFromAttendeeModal from './CancelBookingFromAttendeeModal';
import NoSeatsAvailableModal from './NoSeatsAvailableModal';

type Props = {
	booking: BookingDetail;
	onChange: (attendees: BookingAttendeeResource[]) => void;
	onRequestCreate: () => Promise<BookingDetail>;
	onAddAttendee: (attendee: BookingAttendeeResource) => Promise<void>;
	onUpdateAttendee: (
		attendeeId: number,
		attendee: BookingAttendeeResource,
	) => Promise<void>;
	isAddingAttendee: boolean;
	isUpdatingAttendee: boolean;
	onCancelAttendee: (
		attendee: BookingAttendeeResource,
		options: { sendMail: boolean; cancellationAmountCents: number },
	) => Promise<void>;
	onCancelBooking: (options: { sendMail: boolean }) => Promise<void>;
};

const AttendeeSection = ({
	booking,
	onChange,
	onRequestCreate,
	onAddAttendee,
	onUpdateAttendee,
	isAddingAttendee,
	isUpdatingAttendee,
	onCancelAttendee,
	onCancelBooking,
}: Props) => {
	const [editingIndex, setEditingIndex] = useState<number | null>(null);
	const [isCreating, setIsCreating] = useState(false);
	const [cancellingIndex, setCancellingIndex] = useState<number | null>(null);
	const [isBookingCancelOpen, setIsBookingCancelOpen] = useState(false);
	const [isNoSeatsDialogOpen, setIsNoSeatsDialogOpen] = useState(false);
	const [isCheckingAvailability, setIsCheckingAvailability] = useState(false);
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
		setIsNoSeatsDialogOpen(false);
	};

	const handleCreate = async (attendee: BookingAttendeeResource) => {
		await onAddAttendee(attendee);
		closeModal();
	};

	const handleUpdate = async (attendee: BookingAttendeeResource) => {
		if (editingIndex === null) return;
		const attendeeId = booking.attendees[editingIndex]?.id;
		if (attendeeId === null || attendeeId === undefined) {
			onChange(
				booking.attendees.map((current, index) =>
					index === editingIndex ? attendee : current,
				),
			);
			closeModal();
			return;
		}

		await onUpdateAttendee(attendeeId, attendee);
		closeModal();
	};

	const activeAttendee =
		editingIndex === null ? null : (booking.attendees[editingIndex] ?? null);
	const cancellingAttendee =
		cancellingIndex === null
			? null
			: (booking.attendees[cancellingIndex] ?? null);
	const hasAnyAddableTicket = booking.availableTickets.some(
		(ticket) => ticket.bookingLimit === null || ticket.bookingLimit > 0,
	);

	return (
		<Panel header={__('Attendees', 'ctx-events')}>
			<PanelBody>
				<table className="booking-edit__attendees">
					<thead>
						<tr>
							<th>{__('Ticket', 'ctx-events')}</th>
							<th>{__('Name', 'ctx-events')}</th>
							<th>{__('Status', 'ctx-events')}</th>
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
									<td>{fullName}</td>
									<td>{statusLabel}</td>
									<td>
										<b>{formatPrice(attendee.ticketPrice)}</b>
									</td>
									<td className="booking-edit__attendee-actions">
										<Button
											variant="link"
											onClick={() => setEditingIndex(index)}
											disabled={!isActive}
										>
											<Icon icon={pencil} color="#575858" />
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
												<Icon icon={trash} color="#575858" />
											</Button>
										) : (
											<Button
												variant="link"
												isDestructive
												onClick={() => removeAttendee(index)}
											>
												<Icon icon={trash} color="#575858" />
											</Button>
										)}
									</td>
								</tr>
							);
						})}
					</tbody>
					<tfoot>
						<tr className="booking-edit__attendees-summary">
							<td colSpan={3}>{__('Subtotal', 'ctx-events')}</td>
							<td>{formatPrice(booking.price.bookingPrice)}</td>
							<td></td>
						</tr>
						{booking.price.donationAmount.amountCents > 0 && (
							<tr className="booking-edit__attendees-summary">
								<td colSpan={3}>{__('Donation', 'ctx-events')}</td>
								<td>{formatPrice(booking.price.donationAmount)}</td>
								<td></td>
							</tr>
						)}
						{discountAmountCents > 0 && (
							<tr className="booking-edit__attendees-summary">
								<td colSpan={3}>{__('Coupon / Discount', 'ctx-events')}</td>
								<td>
									-{formatPrice({ amountCents: discountAmountCents, currency })}
								</td>
								<td></td>
							</tr>
						)}
						<tr className="booking-edit__attendees-summary booking-edit__attendees-summary--total">
							<td colSpan={3}>{__('Total', 'ctx-events')}</td>
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
							onClick={async () => {
								setIsCheckingAvailability(true);
								try {
									const refreshedBooking = await onRequestCreate();
									const canAdd = refreshedBooking.availableTickets.some(
										(ticket) =>
											ticket.bookingLimit === null ||
											ticket.bookingLimit > 0,
									);

									if (!canAdd) {
										setIsNoSeatsDialogOpen(true);
										return;
									}

									setIsCreating(true);
								} finally {
									setIsCheckingAvailability(false);
								}
							}}
							disabled={isCheckingAvailability}
						>
							{isCheckingAvailability
								? __('Checking…', 'ctx-events')
								: __('Add Attendee', 'ctx-events')}
						</Button>
						{!hasAnyAddableTicket && (
							<span className="booking-edit__empty">
								{__('No seats currently available.', 'ctx-events')}
							</span>
						)}
					</div>
				)}

				{(isCreating || editingIndex !== null) && (
					<AttendeeEditModal
						attendee={isCreating ? null : activeAttendee}
						booking={booking}
						onClose={closeModal}
						isSaving={isCreating ? isAddingAttendee : isUpdatingAttendee}
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

				{isNoSeatsDialogOpen ? (
					<NoSeatsAvailableModal onClose={closeModal} />
				) : null}
			</PanelBody>
		</Panel>
	);
};

export default AttendeeSection;
