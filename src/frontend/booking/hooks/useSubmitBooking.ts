import apiFetch from '@wordpress/api-fetch';
import { useState } from '@wordpress/element';
import type {
	AttendeePayload,
	BookingCreatedResponse,
	SubmitResult,
} from '../types';

type State = 'idle' | 'loading' | 'done';

type SubmitPayload = {
	token: string;
	event_id: number;
	registration: Record<string, unknown>;
	attendees: AttendeePayload[];
	gateway: string;
	coupon_code?: string;
	donation_amount?: number;
};

export function useSubmitBooking() {
	const [status, setStatus] = useState<State>('idle');

	async function submit(payload: SubmitPayload): Promise<SubmitResult> {
		setStatus('loading');

		try {
			const response = await apiFetch<BookingCreatedResponse>({
				path: '/events/v3/bookings',
				method: 'POST',
				data: payload,
			});

			setStatus('done');

			return {
				type: 'success',
				reference: response.reference ?? '',
				payment: response.payment ?? null,
				customerEmailStatus: response.customerEmailStatus ?? 'unknown',
			};
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
