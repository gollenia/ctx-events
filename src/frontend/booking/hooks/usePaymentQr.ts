import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';

import type { PaymentQrResponse } from '../types';

type State =
	| { status: 'idle'; qr: null; message: '' }
	| { status: 'loading'; qr: null; message: '' }
	| { status: 'loaded'; qr: PaymentQrResponse; message: '' }
	| { status: 'error'; qr: null; message: string };

export function usePaymentQr(reference: string | null, enabled: boolean) {
	const [state, setState] = useState<State>({
		status: 'idle',
		qr: null,
		message: '',
	});

	useEffect(() => {
		if (!enabled || !reference) {
			setState({ status: 'idle', qr: null, message: '' });
			return;
		}

		let cancelled = false;

		async function load() {
			setState({ status: 'loading', qr: null, message: '' });

			try {
				const response = await apiFetch<PaymentQrResponse>({
					path: `/events/v3/payments/bookings/${reference}/qr`,
				});

				if (cancelled) {
					return;
				}

				setState({ status: 'loaded', qr: response, message: '' });
			} catch (err: unknown) {
				if (cancelled) {
					return;
				}

				const message =
					err instanceof Error
						? err.message
						: typeof err === 'object' && err !== null && 'message' in err
							? String((err as { message: unknown }).message)
							: 'Failed to load payment QR code.';

				setState({ status: 'error', qr: null, message });
			}
		}

		void load();

		return () => {
			cancelled = true;
		};
	}, [reference, enabled]);

	return state;
}
