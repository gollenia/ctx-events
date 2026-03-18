import { formatPrice } from '@events/i18n';
import {
	Notice,
	SelectControl,
	Spinner,
	TextControl,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type { BookingAttendeeResource, BookingDetail } from 'src/types/types';
import type { BookingTransactionResource } from 'src/types/types';
import { STATUS_LABELS } from '../constants';
import AttendeeSection from './AttendeeSection';
import DynamicFieldsGrid from './DynamicFieldsGrid';
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
		addingNote,
		addNote,
		resolvingPaymentLink,
		resolvePaymentLink,
	} = useBookingDetail(reference);

	const [booking, setBooking] = useState<BookingDetail | null>(null);
	const [registration, setRegistration] = useState<BookingFormValues>({});
	const [saveError, setSaveError] = useState<string | null>(null);
	console.log(booking);
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
				new Date(right.createdAt).getTime() - new Date(left.createdAt).getTime(),
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
				availableTickets: nextBooking.availableTickets,
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
					<button
						type="button"
						className="booking-edit-modal__close"
						onClick={onClose}
						aria-label={__('Close', 'ctx-events')}
					>
						×
					</button>
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
								<section className="booking-edit__section">
									<h3>{__('Booking Info', 'ctx-events')}</h3>

									<div className="booking-edit__meta">
										<span>
											<strong>{__('Status', 'ctx-events')}:</strong>{' '}
											{STATUS_LABELS[booking.status] ?? booking.status}
										</span>
										<span>
											<strong>{__('Date', 'ctx-events')}:</strong>{' '}
											{new Date(booking.date).toLocaleString()}
										</span>
										<span>
											<strong>{__('Total', 'ctx-events')}:</strong>{' '}
											{formatPrice(booking.price.finalPrice)}
										</span>
									</div>

									<DynamicFieldsGrid
										fields={registrationFields}
										values={registration}
										onChange={patchRegistration}
										gridClassName="booking-edit__registration-grid"
										fieldClassName="booking-edit__registration-grid-field"
										inputWrapClassName="booking-edit__field-input-wrap"
									/>

									<SelectControl
										label={__('Gateway', 'ctx-events')}
										value={booking.gateway ?? ''}
										options={[
											{ value: '', label: __('— None —', 'ctx-events') },
											...availableGateways,
										]}
										onChange={(value) => patch({ gateway: value || null })}
									/>

									<TextControl
										label={__('Donation', 'ctx-events')}
										type="number"
										value={String(
											(booking.price.donationAmount.amountCents ?? 0) / 100,
										)}
										onChange={(value) => {
											if (!booking) return;

											const amount = Number.parseFloat(value);
											const donationCents = Number.isFinite(amount)
												? Math.round(amount * 100)
												: 0;

											patchBookingPrice(booking, {
												price: {
													...booking.price,
													donationAmount: {
														...booking.price.donationAmount,
														amountCents: donationCents,
													},
												},
											});
										}}
									/>
								</section>

								<NotesSection
									booking={booking}
									isSaving={addingNote}
									onAdd={async (text: string) => {
										const note = await addNote(booking.reference, text);
										patch({ notes: [...booking.notes, note] });
									}}
								/>

								<LogEntriesSection booking={booking} />
							</div>

							<div className="booking-edit__attendees-col">
								<AttendeeSection
									booking={booking}
									onChange={(attendees: BookingAttendeeResource[]) =>
										patchBookingPrice(booking, { attendees })
									}
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
							</div>
						</div>

						{saveError && (
							<Notice status="error" isDismissible={false}>
								{saveError}
							</Notice>
						)}

						<div className="booking-edit__footer">
							<button
								type="button"
								className="components-button is-secondary"
								onClick={onClose}
							>
								{__('Cancel', 'ctx-events')}
							</button>
							<button
								type="button"
								className="components-button is-primary"
								onClick={handleSave}
								disabled={saving}
								aria-busy={saving}
							>
								{saving
									? __('Saving…', 'ctx-events')
									: __('Save', 'ctx-events')}
							</button>
						</div>
					</div>
				)}
			</div>
		</div>
	);
};

export default BookingEditModal;
