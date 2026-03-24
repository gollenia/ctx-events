import apiFetch from '@wordpress/api-fetch';
import { useCallback, useEffect, useState } from '@wordpress/element';
import type { BookingData, TicketInfo } from '../types';

type State =
	| { status: 'idle' }
	| { status: 'loading' }
	| { status: 'loaded'; data: BookingData }
	| { status: 'error'; message: string };

function toNumber(value: unknown, fallback = 0): number {
	const parsed =
		typeof value === 'number'
			? value
			: typeof value === 'string'
				? Number(value)
				: Number.NaN;

	return Number.isFinite(parsed) ? parsed : fallback;
}

function toNullableNumber(value: unknown): number | null {
	if (value === null || value === undefined || value === '') {
		return null;
	}

	const parsed =
		typeof value === 'number'
			? value
			: typeof value === 'string'
				? Number(value)
				: Number.NaN;

	return Number.isFinite(parsed) ? parsed : null;
}

function mapTicket(raw: unknown): TicketInfo | null {
	if (typeof raw !== 'object' || raw === null) {
		return null;
	}

	const ticket = raw as Record<string, unknown>;

	return {
		id: String(ticket.id ?? ''),
		name: String(ticket.name ?? ''),
		price_in_cents: toNumber(ticket.price_in_cents),
		currency: String(ticket.currency ?? 'EUR'),
		available_quantity: toNumber(ticket.available_quantity),
		ticket_limit_per_booking: toNullableNumber(ticket.ticket_limit_per_booking),
		booking_limit: toNullableNumber(ticket.booking_limit),
	};
}

export function useBookingData(postId: number | null) {
	const [state, setState] = useState<State>({ status: 'idle' });

	useEffect(() => {
		setState({ status: 'idle' });
	}, [postId]);

	const load = useCallback(async () => {
		if (!postId) return;
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
				tickets: Array.isArray(raw.tickets)
					? raw.tickets
							.map((ticket) => mapTicket(ticket))
							.filter((ticket): ticket is TicketInfo => ticket !== null)
					: [],
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
