import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';
import type { BookingDetail, BookingNoteResource } from 'src/types/types';

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

	useEffect(() => {
		if (!reference) {
			setBooking(null);
			setError(null);
			return;
		}

		setLoading(true);
		setError(null);

		apiFetch<BookingDetail>({ path: `/events/v3/bookings/${reference}` })
			.then(setBooking)
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

	return { booking, loading, error, saving, save, addingNote, addNote };
};
