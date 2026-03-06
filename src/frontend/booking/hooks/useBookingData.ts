import apiFetch from '@wordpress/api-fetch';
import { useCallback, useState } from '@wordpress/element';
import type { BookingData } from '../types';

type State =
	| { status: 'idle' }
	| { status: 'loading' }
	| { status: 'loaded'; data: BookingData }
	| { status: 'error'; message: string };

export function useBookingData(postId: number) {
	const [state, setState] = useState<State>({ status: 'idle' });

	const load = useCallback(async () => {
		if (state.status === 'loading' || state.status === 'loaded') return;

		setState({ status: 'loading' });

		try {
			const raw = await apiFetch<Record<string, unknown>>({
				path: `/events/v3/events/${postId}/prepare-booking`,
			});

			const data: BookingData = {
				eventName: String(raw.eventName ?? ''),
				eventStartDate: String(raw.eventStartDate ?? ''),
				eventEndDate: String(raw.eventEndDate ?? ''),
				eventDescription: String(raw.eventDescription ?? ''),
				tickets: Array.isArray(raw.tickets) ? raw.tickets : [],
				gateways: Array.isArray(raw.gateways) ? raw.gateways : [],
				bookingForm: (raw.bookingForm as BookingData['bookingForm']) ?? { id: 0, type: 'booking', name: '', description: null, fields: [] },
				attendeeForm: (raw.attendeeForm as BookingData['attendeeForm']) ?? null,
				couponsEnabled: Boolean(raw.couponsEnabled),
				token: String(raw.token ?? ''),
			};

			setState({ status: 'loaded', data });
		} catch (err: unknown) {
			const message =
				err instanceof Error
					? err.message
					: typeof err === 'object' && err !== null && 'message' in err
						? String((err as { message: unknown }).message)
						: 'Failed to load booking data.';
			setState({ status: 'error', message });
		}
	}, [postId, state.status]);

	return { state, load };
}
