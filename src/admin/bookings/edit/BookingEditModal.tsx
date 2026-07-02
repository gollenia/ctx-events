import {
	Flex,
	Notice,
	Spinner,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { __, _x } from '@wordpress/i18n';
import type {
	BookingAttendeeResource,
	BookingDetail,
	BookingTransactionResource,
} from 'src/types/types';
import AttendeeSection from './AttendeeSection';
import BookingInfoPanel from './BookingInfoPanel';
import {
	type BookingFormValues,
	getFallbackRegistrationFields,
} from './formFields';
import LogEntriesSection from './LogEntriesSection';
import NotesSection from './NotesSection';
import { calculateBookingPriceSummary } from './priceSummary';
import TransactionSection from './TransactionSection';
import { useBookingDetail } from './useBookingDetail';

type Props = {
	reference: string | null;
	availableGateways: Array<{ value: string; label: string }>;
	onClose: () => void;
	onSaved: () => void;
};

const createRegistrationDraft = (
	booking: BookingDetail,
): BookingFormValues => ({
	...booking.registration,
	email: booking.registration.email ?? booking.email,
	first_name: booking.registration.first_name ?? booking.name.firstName,
	last_name: booking.registration.last_name ?? booking.name.lastName,
});

const BookingEditModal = ({
	reference,
	availableGateways,
	onClose,
	onSaved,
}: Props) => {
	const {
		booking: fetchedBooking,
		loading,
		error,
		saving,
		save,
		refresh,
		addingNote,
		addNote,
		resolvingPaymentLink,
		resolvePaymentLink,
		addingAttendee,
		addAttendee,
		updatingAttendee,
		updateAttendee,
		cancellingAttendee,
		cancelAttendee,
		cancellingBooking,
		cancelBooking,
	} = useBookingDetail(reference);

	const [booking, setBooking] = useState<BookingDetail | null>(null);
	const [registration, setRegistration] = useState<BookingFormValues>({});
	const [saveError, setSaveError] = useState<string | null>(null);

	useEffect(() => {
		if (fetchedBooking) {
			setBooking(fetchedBooking);
			setRegistration(createRegistrationDraft(fetchedBooking));
		}
	}, [fetchedBooking]);

	const patch = (fields: Partial<BookingDetail>) =>
		setBooking((prev) => (prev ? { ...prev, ...fields } : prev));

	const mergeTransaction = (
		currentTransactions: BookingDetail['transactions'],
		transaction: BookingTransactionResource,
	): BookingDetail['transactions'] => {
		const withoutCurrent = currentTransactions.filter(
			(item) => item.externalId !== transaction.externalId,
		);

		return [transaction, ...withoutCurrent].sort(
			(left, right) =>
				new Date(right.createdAt).getTime() -
				new Date(left.createdAt).getTime(),
		);
	};

	const patchBookingPrice = (
		currentBooking: BookingDetail,
		fields: Partial<BookingDetail>,
	) => {
		const nextBooking = { ...currentBooking, ...fields };
		const donationCents = nextBooking.price.donationAmount.amountCents ?? 0;

		setBooking({
			...nextBooking,
			price: calculateBookingPriceSummary({
				attendees: nextBooking.attendees,
				currentPrice: nextBooking.price,
				donationCents,
			}),
		});
	};

	const patchRegistration = (key: string, value: unknown) =>
		setRegistration((prev) => ({ ...prev, [key]: value }));

	const handleSave = async () => {
		if (!booking) return;
		setSaveError(null);
		try {
			await save(booking, registration);
			onSaved();
			onClose();
		} catch (err: any) {
			setSaveError(err?.message ?? __('Could not save booking.', 'ctx-events'));
		}
	};

	const title = booking
		? `${booking.event.title}`
		: __('Booking', 'ctx-events');
	const registrationFields =
		booking && booking.bookingForm.fields.length > 0
			? booking.bookingForm.fields
			: getFallbackRegistrationFields();

	useEffect(() => {
		const onEscape = (event: KeyboardEvent) => {
			if (event.key === 'Escape') {
				onClose();
			}
		};

		document.addEventListener('keydown', onEscape);
		document.body.classList.add('booking-edit-open');

		return () => {
			document.removeEventListener('keydown', onEscape);
			document.body.classList.remove('booking-edit-open');
		};
	}, [onClose]);

	if (!reference) return null;

	return (
		<div
			className="booking-edit-shell"
			role="dialog"
			aria-modal="true"
			aria-label={title}
		>
			<div className="booking-edit-modal">
				<div className="booking-edit-modal__header">
					<h2 className="booking-edit-modal__title">{title}</h2>
					<div className="booking-edit__footer">
						<button
							type="button"
							className="components-button is-secondary"
							onClick={onClose}
						>
							{_x('Close', 'dialog action: close modal', 'ctx-events')}
						</button>
					</div>
				</div>
				{loading && (
					<div className="booking-edit__loading">
						<Spinner />
					</div>
				)}

				{error && (
					<Notice status="error" isDismissible={false}>
						{error}
					</Notice>
				)}

				{booking && (
					<div className="booking-edit__body">
						<div className="booking-edit__columns">
							<div className="booking-edit__info">
								<BookingInfoPanel
									booking={booking}
									registration={registration}
									registrationFields={registrationFields}
									availableGateways={availableGateways}
									isSaving={
										saving ||
										addingAttendee ||
										updatingAttendee ||
										cancellingAttendee ||
										cancellingBooking
									}
									onRegistrationChange={patchRegistration}
									onGatewayChange={(gateway) => patch({ gateway })}
									onDonationChange={(donationCents) =>
										patchBookingPrice(booking, {
											price: {
												...booking.price,
												donationAmount: {
													...booking.price.donationAmount,
													amountCents: donationCents,
												},
											},
										})
									}
									onSave={handleSave}
								/>
							</div>

							<Flex
								direction="column"
								gap={4}
								className="booking-edit__details"
							>
								<AttendeeSection
									booking={booking}
									onChange={(attendees: BookingAttendeeResource[]) =>
										patchBookingPrice(booking, { attendees })
									}
									onRequestCreate={async () => {
										const refreshedBooking = await refresh(booking.reference);
										setBooking(refreshedBooking);
										setRegistration(createRegistrationDraft(refreshedBooking));

										return refreshedBooking;
									}}
									onAddAttendee={async (attendee) => {
										setSaveError(null);

										try {
											const updatedBooking = await addAttendee(
												booking.reference,
												attendee,
											);
											setBooking(updatedBooking);
											setRegistration(createRegistrationDraft(updatedBooking));
										} catch (err: any) {
											setSaveError(
												err?.message ??
													__('Could not add attendee.', 'ctx-events'),
											);
											throw err;
										}
									}}
									onUpdateAttendee={async (attendeeId, attendee) => {
										setSaveError(null);

										try {
											const updatedBooking = await updateAttendee(
												booking.reference,
												attendeeId,
												attendee,
											);
											setBooking(updatedBooking);
											setRegistration(createRegistrationDraft(updatedBooking));
										} catch (err: any) {
											setSaveError(
												err?.message ??
													__('Could not update attendee.', 'ctx-events'),
											);
											throw err;
										}
									}}
									isAddingAttendee={addingAttendee}
									isUpdatingAttendee={updatingAttendee}
									onCancelAttendee={async (attendee, options) => {
										if (attendee.id === null) {
											return;
										}

										setSaveError(null);

										try {
											const updatedBooking = await cancelAttendee(
												booking.reference,
												attendee.id,
												options,
											);
											setBooking(updatedBooking);
											setRegistration(createRegistrationDraft(updatedBooking));
										} catch (err: any) {
											setSaveError(
												err?.message ??
													__('Could not cancel attendee.', 'ctx-events'),
											);
											throw err;
										}
									}}
									onCancelBooking={async (options) => {
										setSaveError(null);

										try {
											const updatedBooking = await cancelBooking(
												booking.reference,
												options,
											);
											setBooking(updatedBooking);
											setRegistration(createRegistrationDraft(updatedBooking));
										} catch (err: any) {
											setSaveError(
												err?.message ??
													__('Could not cancel booking.', 'ctx-events'),
											);
											throw err;
										}
									}}
								/>
								<TransactionSection
									booking={booking}
									isResolvingPaymentLink={resolvingPaymentLink}
									onResolvePaymentLink={async () => {
										const transaction = await resolvePaymentLink(
											booking.reference,
										);
										patch({
											transactions: mergeTransaction(
												booking.transactions,
												transaction,
											),
										});

										return transaction;
									}}
								/>

								<NotesSection
									booking={booking}
									isSaving={addingNote}
									onAdd={async (text: string) => {
										const note = await addNote(booking.reference, text);
										patch({ notes: [...booking.notes, note] });
									}}
								/>

								<LogEntriesSection booking={booking} />
							</Flex>
						</div>

						{saveError && (
							<Notice status="error" isDismissible={false}>
								{saveError}
							</Notice>
						)}
					</div>
				)}
			</div>
		</div>
	);
};

export default BookingEditModal;
