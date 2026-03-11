import { formatPrice } from '@events/i18n';
import {
	Button,
	Modal,
	Notice,
	SelectControl,
	Spinner,
	TextControl,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type { BookingAttendeeResource, BookingDetail, BookingNoteResource } from 'src/types/types';
import AttendeeSection from './AttendeeSection';
import NotesSection from './NotesSection';
import { useBookingDetail } from './useBookingDetail';

type BookingRegistration = Record<string, string>;

const STATUS_LABELS: Record<number, string> = {
	1: __('Pending', 'ctx-events'),
	2: __('Approved', 'ctx-events'),
	3: __('Canceled', 'ctx-events'),
	4: __('Expired', 'ctx-events'),
	9: __('Deleted', 'ctx-events'),
};

type Props = {
	reference: string | null;
	availableGateways: Array<{ value: string; label: string }>;
	onClose: () => void;
	onSaved: () => void;
};

const BookingEditModal = ({ reference, availableGateways, onClose, onSaved }: Props) => {
	const { booking: fetchedBooking, loading, error, saving, save } = useBookingDetail(reference);

	const [booking, setBooking] = useState<BookingDetail | null>(null);
	const [registration, setRegistration] = useState<BookingRegistration>({});
	const [saveError, setSaveError] = useState<string | null>(null);

	useEffect(() => {
		if (fetchedBooking) {
			setBooking(fetchedBooking);
			setRegistration(fetchedBooking.registration as BookingRegistration);
		}
	}, [fetchedBooking]);

	if (!reference) return null;

	const patch = (fields: Partial<BookingDetail>) =>
		setBooking((prev) => prev ? { ...prev, ...fields } : prev);

	const patchRegistration = (key: string, value: string) =>
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
		? `${booking.reference} – ${booking.event.title}`
		: __('Booking', 'ctx-events');

	return (
		<Modal title={title} size="fill" onRequestClose={onClose} className="booking-edit-modal">
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
										{formatPrice({ amountCents: booking.price.finalPrice, currency: booking.price.currency })}
									</span>
								</div>

								<TextControl
									label={__('First Name', 'ctx-events')}
									value={registration.first_name ?? ''}
									onChange={(value) => patchRegistration('first_name', value)}
								/>
								<TextControl
									label={__('Last Name', 'ctx-events')}
									value={registration.last_name ?? ''}
									onChange={(value) => patchRegistration('last_name', value)}
								/>
								<TextControl
									label={__('E-Mail', 'ctx-events')}
									type="email"
									value={registration.email ?? booking.email}
									onChange={(value) => patchRegistration('email', value)}
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
									value={String(booking.price.donationAmount / 100)}
									onChange={(value) =>
										patch({
											price: {
												...booking.price,
												donationAmount: Math.round(parseFloat(value) * 100) || 0,
											},
										})
									}
								/>
							</section>

							<NotesSection
								booking={booking}
								onChange={(notes: BookingNoteResource[]) => patch({ notes })}
							/>
						</div>

						<div className="booking-edit__attendees-col">
							<AttendeeSection
								booking={booking}
								onChange={(attendees: BookingAttendeeResource[]) => patch({ attendees })}
							/>
						</div>
					</div>

					{saveError && (
						<Notice status="error" isDismissible={false}>
							{saveError}
						</Notice>
					)}

					<div className="booking-edit__footer">
						<Button variant="secondary" onClick={onClose}>
							{__('Cancel', 'ctx-events')}
						</Button>
						<Button variant="primary" onClick={handleSave} isBusy={saving}>
							{__('Save', 'ctx-events')}
						</Button>
					</div>
				</div>
			)}
		</Modal>
	);
};

export default BookingEditModal;
