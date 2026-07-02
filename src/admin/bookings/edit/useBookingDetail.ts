import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';
import type {
	BookingDetail,
	BookingNoteResource,
	BookingTransactionResource,
} from 'src/types/types';

type SavePayload = {
	registration: Record<string, unknown>;
	attendees: BookingDetail['attendees'];
	donation_cents: number;
	gateway?: string;
};

export const useBookingDetail = (reference: string | null) => {
	const [booking, setBooking] = useState<BookingDetail | null>(null);
	const [loading, setLoading] = useState(false);
	const [error, setError] = useState<string | null>(null);
	const [saving, setSaving] = useState(false);
	const [addingNote, setAddingNote] = useState(false);
	const [resolvingPaymentLink, setResolvingPaymentLink] = useState(false);
	const [addingAttendee, setAddingAttendee] = useState(false);
	const [updatingAttendee, setUpdatingAttendee] = useState(false);
	const [cancellingAttendee, setCancellingAttendee] = useState(false);
	const [cancellingBooking, setCancellingBooking] = useState(false);

	const loadBooking = async (bookingReference: string): Promise<BookingDetail> => {
		const nextBooking = await apiFetch<BookingDetail>({
			path: `/events/v3/bookings/${bookingReference}`,
		});
		setBooking(nextBooking);

		return nextBooking;
	};

	useEffect(() => {
		if (!reference) {
			setBooking(null);
			setError(null);
			return;
		}

		setLoading(true);
		setError(null);

		loadBooking(reference)
			.catch((err: any) => setError(err?.message ?? 'Unknown error'))
			.finally(() => setLoading(false));
	}, [reference]);

	const save = async (
		updated: BookingDetail,
		registration: Record<string, unknown>,
	): Promise<void> => {
		setSaving(true);
		try {
			await apiFetch({
				path: `/events/v3/bookings/${updated.reference}`,
				method: 'PUT',
					data: {
						registration,
						attendees: updated.attendees,
						donation_cents: updated.price.donationAmount.amountCents ?? 0,
						...(updated.gateway !== null ? { gateway: updated.gateway } : {}),
					} satisfies SavePayload,
				});
		} finally {
			setSaving(false);
		}
	};

	const addNote = async (reference: string, text: string): Promise<BookingNoteResource> => {
		setAddingNote(true);
		try {
			return await apiFetch<BookingNoteResource>({
				path: `/events/v3/bookings/${reference}/notes`,
				method: 'POST',
				data: { text },
			});
		} finally {
			setAddingNote(false);
		}
	};

	const addAttendee = async (
		bookingReference: string,
		attendee: BookingDetail['attendees'][number],
	): Promise<BookingDetail> => {
		setAddingAttendee(true);
		try {
			await apiFetch({
				path: `/events/v3/bookings/${bookingReference}/attendees`,
				method: 'POST',
				data: { attendee },
			});

			return await loadBooking(bookingReference);
		} finally {
			setAddingAttendee(false);
		}
	};

	const updateAttendee = async (
		bookingReference: string,
		attendeeId: number,
		attendee: BookingDetail['attendees'][number],
	): Promise<BookingDetail> => {
		setUpdatingAttendee(true);
		try {
			await apiFetch({
				path: `/events/v3/bookings/${bookingReference}/attendees/${attendeeId}`,
				method: 'PUT',
				data: { attendee },
			});

			return await loadBooking(bookingReference);
		} finally {
			setUpdatingAttendee(false);
		}
	};

	const resolvePaymentLink = async (
		reference: string,
	): Promise<BookingTransactionResource> => {
		setResolvingPaymentLink(true);
		try {
			return await apiFetch<BookingTransactionResource>({
				path: `/events/v3/bookings/${reference}/payment-link`,
				method: 'POST',
			});
		} finally {
			setResolvingPaymentLink(false);
		}
	};

	const cancelAttendee = async (
		bookingReference: string,
		attendeeId: number,
		options: {
			sendMail: boolean;
			cancellationAmountCents: number;
		},
	): Promise<BookingDetail> => {
		setCancellingAttendee(true);
		try {
			await apiFetch({
				path: `/events/v3/bookings/${bookingReference}/attendees/${attendeeId}/cancel`,
				method: 'POST',
				data: {
					sendmail: options.sendMail,
					cancellation_amount_cents: options.cancellationAmountCents,
				},
			});

			return await loadBooking(bookingReference);
		} finally {
			setCancellingAttendee(false);
		}
	};

	const cancelBooking = async (
		bookingReference: string,
		options: {
			sendMail: boolean;
			cancellationReason?: string;
		},
	): Promise<BookingDetail> => {
		setCancellingBooking(true);
		try {
			await apiFetch({
				path: `/events/v3/bookings/${bookingReference}/cancel`,
				method: 'POST',
				data: {
					sendmail: options.sendMail,
					cancellation_reason: options.cancellationReason ?? '',
				},
			});

			return await loadBooking(bookingReference);
		} finally {
			setCancellingBooking(false);
		}
	};

	return {
		booking,
		loading,
		error,
		saving,
		save,
		refresh: loadBooking,
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
	};
};
