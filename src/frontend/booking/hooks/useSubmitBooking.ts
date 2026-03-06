import apiFetch from '@wordpress/api-fetch';
import { useState } from '@wordpress/element';
import type { AttendeePayload, SubmitResult } from '../types';

type State = 'idle' | 'loading' | 'done';

type SubmitPayload = {
	token: string;
	event_id: number;
	registration: Record<string, unknown>;
	attendees: AttendeePayload[];
	gateway: string;
	coupon_code?: string;
};

export function useSubmitBooking() {
	const [status, setStatus] = useState<State>('idle');

	async function submit(payload: SubmitPayload): Promise<SubmitResult> {
		setStatus('loading');

		try {
			const response = await apiFetch<{ reference?: string; payment_url?: string }>({
				path: '/events/v3/bookings',
				method: 'POST',
				data: payload,
			});

			setStatus('done');

			if (response.payment_url) {
				return { type: 'mollie', url: response.payment_url };
			}

			return { type: 'success', reference: response.reference ?? '' };
		} catch (err: unknown) {
			setStatus('idle');
			const message =
				err instanceof Error
					? err.message
					: typeof err === 'object' && err !== null && 'message' in err
						? String((err as { message: unknown }).message)
						: 'An error occurred. Please try again.';
			return { type: 'error', message };
		}
	}

	return { status, submit };
}
