import { formatPrice, formatPriceRange } from '@events/i18n/money';
import { store as coreStore } from '@wordpress/core-data';

export type BindingConfig = {
	args?: {
		field?: string;
	};
};

export type Context = {
	postId?: number;
	postType?: string;
	'ctx-events/eventId'?: number;
};

export type EventTicket = {
	ticket_price?: number | string;
	ticket_enabled?: boolean;
};

export type EventRecord = {
	title?: { raw?: string; rendered?: string };
	excerpt?: { rendered?: string };
	featured_media?: number;
	link?: string;
	meta?: {
		_event_start?: string;
		_event_end?: string;
		_booking_enabled?: boolean | number | string;
		_booking_currency?: string;
		_booking_start?: string;
		_booking_end?: string;
		_location_id?: number;
		_person_id?: number | string | number[];
		_event_tickets?: EventTicket[];
	};
	_embedded?: {
		'wp:featuredmedia'?: Array<{
			id?: number;
			alt_text?: string;
			source_url?: string;
		}>;
	};
};

export type RelatedRecord = {
	title?: { raw?: string; rendered?: string };
	link?: string;
	_embedded?: {
		'wp:featuredmedia'?: Array<{
			id?: number;
			alt_text?: string;
			source_url?: string;
		}>;
	};
};

export type BindingField = {
	label: string;
	type: 'string' | 'number';
	args: {
		field: string;
	};
};

export function stripHtml(html?: string): string {
	if (!html) {
		return '';
	}

	return html.replace(/<[^>]+>/g, '').trim();
}

export function getEventFromContext(
	select: unknown,
	context?: Context,
): EventRecord | null {
	const selectedEventId = Number(context?.['ctx-events/eventId'] ?? 0);
	const fallbackEventId =
		context?.postType === 'ctx-event' ? Number(context?.postId ?? 0) : 0;
	const eventId = selectedEventId || fallbackEventId;

	if (!eventId) {
		return null;
	}

	return (
		select as (store: typeof coreStore) => {
			getEntityRecord: (
				kind: string,
				name: string,
				id: number,
				query?: Record<string, unknown>,
			) => EventRecord | null;
		}
	)(coreStore).getEntityRecord('postType', 'ctx-event', eventId, {
		_embed: true,
	});
}

export function getRelatedRecord(
	select: unknown,
	postType: string,
	recordId: number,
): RelatedRecord | null {
	if (!recordId) {
		return null;
	}

	return (
		select as (store: typeof coreStore) => {
			getEntityRecord: (
				kind: string,
				name: string,
				id: number,
				query?: Record<string, unknown>,
			) => RelatedRecord | null;
		}
	)(coreStore).getEntityRecord('postType', postType, recordId, {
		_embed: true,
	});
}

export function getRecordTitle(record: RelatedRecord | null): string {
	return record?.title?.raw || record?.title?.rendered || '';
}

export function getPersonRecordId(event: EventRecord | null): number {
	const value = event?.meta?._person_id;

	return Number(Array.isArray(value) ? value[0] ?? 0 : value ?? 0);
}

export function getPriceLabel(
	event: EventRecord | null,
	freeLabel: string,
): string {
	const tickets = Array.isArray(event?.meta?._event_tickets)
		? event?.meta?._event_tickets
		: [];
	console.log('Tickets:', tickets);
	const prices = tickets
		.filter((ticket) => ticket?.ticket_enabled !== false)
		.map((ticket) => Number(ticket.ticket_price ?? 0))
		.filter((value) => Number.isFinite(value));

	if (prices.length === 0) {
		return '';
	}

	const min = Math.min(...prices);
	const max = Math.max(...prices);

	if (min === 0 && max === 0) {
		return freeLabel;
	}

	const currency = event?.meta?._booking_currency || 'EUR';
	const minPrice = { amountCents: min, currency };
	const maxPrice = { amountCents: max, currency };

	return min === max
		? formatPrice(minPrice)
		: formatPriceRange(minPrice, maxPrice);
}
