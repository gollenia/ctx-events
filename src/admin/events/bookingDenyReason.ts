import { formatDate } from '@events/i18n';
import { EventBookingSummary } from '../../types/types';
import { __ } from '@wordpress/i18n';

enum BookingDenyReason {
	DISABLED = 'disabled',
	NO_TICKETS = 'no_tickets',
	ENDED = 'ended',
	NOT_STARTED = 'not_started',
	SOLD_OUT = 'sold_out'
}

export function bookingDenyReason(bookingSummary: EventBookingSummary): string {

	const $reason = bookingSummary?.denyReason;	
	switch ($reason) {
		case BookingDenyReason.DISABLED:
			return __('Bookings are disabled.', 'ctx-events');
		case BookingDenyReason.NO_TICKETS:
			return __('No tickets available.', 'ctx-events');
		case BookingDenyReason.ENDED:
			return __('Booking period has ended on ', 'ctx-events') + formatDate(bookingSummary.bookingEnd || '');
		case BookingDenyReason.NOT_STARTED:
			return __('Booking will start on ', 'ctx-events') + formatDate(bookingSummary.bookingStart || '');
		case BookingDenyReason.SOLD_OUT:
			return __('The event is sold out.', 'ctx-events');
		default:
			return __('Unknown reason', 'ctx-events');
	}
}