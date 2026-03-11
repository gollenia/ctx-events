import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';
import type { BookingDetail } from 'src/types/types';

type SavePayload = {
	registration: Record<string, string>;
	attendees: BookingDetail['attendees'];
	donation_cents: number;
	notes: BookingDetail['notes'];
	gateway: string | null;
};

export const useBookingDetail = (reference: string | null) => {
	const [booking, setBooking] = useState<BookingDetail | null>(null);
	const [loading, setLoading] = useState(false);
	const [error, setError] = useState<string | null>(null);
	const [saving, setSaving] = useState(false);

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
	console.log(booking);
	const save = async (
		updated: BookingDetail,
		registration: Record<string, string>,
	): Promise<void> => {
		setSaving(true);
		try {
			await apiFetch({
				path: `/events/v3/bookings/${updated.reference}`,
				method: 'PUT',
				data: {
					registration,
					attendees: updated.attendees,
					donation_cents: updated.price.donationAmount,
					notes: updated.notes,
					gateway: updated.gateway,
				} satisfies SavePayload,
			});
		} finally {
			setSaving(false);
		}
	};

	return { booking, loading, error, saving, save };
};
