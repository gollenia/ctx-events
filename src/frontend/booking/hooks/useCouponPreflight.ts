import apiFetch from '@wordpress/api-fetch';
import { useState } from '@wordpress/element';

import type { CouponCheckResult } from '../types';

type State = 'idle' | 'loading' | 'success' | 'error';

type CouponCheckResponse = {
	name?: string;
	discount_type?: string;
	discount_value?: number;
	discount_amount?: number;
	message?: string;
};

type CheckPayload = {
	code: string;
	eventId: number;
	bookingPrice: number;
	currency: string;
};

export function useCouponPreflight() {
	const [status, setStatus] = useState<State>('idle');
	const [result, setResult] = useState<CouponCheckResult | null>(null);
	const [message, setMessage] = useState('');

	function reset() {
		setStatus('idle');
		setResult(null);
		setMessage('');
	}

	async function check(payload: CheckPayload): Promise<CouponCheckResult | null> {
		setStatus('loading');
		setResult(null);
		setMessage('');

		try {
			const params = new URLSearchParams({
				code: payload.code,
				event_id: String(payload.eventId),
				booking_price: String(payload.bookingPrice),
				currency: payload.currency,
			});

			const response = await apiFetch<CouponCheckResponse>({
				path: `/events/v3/coupons/check?${params.toString()}`,
				method: 'POST',
			});

			const nextResult: CouponCheckResult = {
				name: response.name ?? '',
				discountType: response.discount_type ?? '',
				discountValue: Number(response.discount_value ?? 0),
				discountAmount: Number(response.discount_amount ?? 0),
			};

			setStatus('success');
			setResult(nextResult);

			return nextResult;
		} catch (err: unknown) {
			setStatus('error');
			const nextMessage =
				err instanceof Error
					? err.message
					: typeof err === 'object' && err !== null && 'message' in err
						? String((err as { message: unknown }).message)
						: 'An error occurred. Please try again.';
			setMessage(nextMessage);
			return null;
		}
	}

	return { status, result, message, check, reset };
}
