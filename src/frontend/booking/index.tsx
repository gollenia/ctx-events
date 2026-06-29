import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';
import Booking from './Booking';
import type { PaymentReturnStatus } from './types';

domReady(() => {
	const rootElement = document.getElementById('booking_app');
	if (!rootElement) return;

	const eventId = Number(rootElement.getAttribute('data-ctx-event-id') ?? '0');
	const params = new URLSearchParams(window.location.search);
	const returnReference = params.get('ctx_events_booking_reference');
	const paymentStatus = (params.get('ctx_events_payment_status') ??
		'unknown') as PaymentReturnStatus;

	const root = createRoot(rootElement);

	root.render(
		<Booking
			initialPostId={eventId > 0 ? eventId : null}
			initialReturnState={
				returnReference
					? {
							reference: returnReference,
							paymentStatus,
						}
					: null
			}
		/>,
	);
});
