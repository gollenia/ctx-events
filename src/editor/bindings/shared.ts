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
	'ctx-events/selectionMode'?: 'manual' | 'query' | 'current';
	'ctx-events/queryCategoryIds'?: number[];
	'ctx-events/queryTagIds'?: number[];
	'ctx-events/queryLocationId'?: number;
	'ctx-events/queryScope'?: string;
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
	'ctx-event-categories'?: number[];
	'ctx-event-tags'?: number[];
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

function normalizeIds(values?: number[]): number[] {
	return Array.isArray(values)
		? values.map((value) => Number(value)).filter((value) => Number.isFinite(value) && value > 0)
		: [];
}

function resolveScopeField(event: EventRecord): string {
	return event.meta?._event_end || event.meta?._event_start || '';
}

function matchesScope(event: EventRecord, scope: string): boolean {
	const start = event.meta?._event_start ? new Date(event.meta._event_start) : null;
	const end = event.meta?._event_end ? new Date(event.meta._event_end) : start;
	const compare = resolveScopeField(event);
	const compareDate = compare ? new Date(compare) : null;
	const now = new Date();

	if (!start || Number.isNaN(start.getTime()) || !compareDate || Number.isNaN(compareDate.getTime())) {
		return false;
	}

	switch (scope) {
		case 'today': {
			const today = now.toISOString().slice(0, 10);
			const startDay = start.toISOString().slice(0, 10);
			const endDay = end && !Number.isNaN(end.getTime()) ? end.toISOString().slice(0, 10) : startDay;
			return startDay <= today && endDay >= today;
		}
		case 'this-week': {
			const weekEnd = new Date(now);
			weekEnd.setDate(now.getDate() + 7);
			return compareDate >= now && start <= weekEnd;
		}
		case 'this-month': {
			return (
				start.getFullYear() === now.getFullYear() &&
				start.getMonth() === now.getMonth() &&
				compareDate >= now
			);
		}
		case 'future':
		default:
			return compareDate >= now;
	}
}

function getQueryEventFromContext(
	select: unknown,
	context?: Context,
): EventRecord | null {
	const categories = normalizeIds(context?.['ctx-events/queryCategoryIds']);
	const tags = normalizeIds(context?.['ctx-events/queryTagIds']);
	const locationId = Number(context?.['ctx-events/queryLocationId'] ?? 0);
	const scope = context?.['ctx-events/queryScope'] || 'future';

	const events =
		(
			select as (store: typeof coreStore) => {
				getEntityRecords: (
					kind: string,
					name: string,
					query?: Record<string, unknown>,
				) => EventRecord[] | null;
			}
		)(coreStore).getEntityRecords('postType', 'ctx-event', {
			per_page: -1,
			status: ['publish', 'future'],
			_embed: true,
		}) ?? [];

	const filtered = events
		.filter((event) => {
			if (!matchesScope(event, scope)) {
				return false;
			}

			if (
				locationId > 0 &&
				Number(event.meta?._location_id ?? 0) !== locationId
			) {
				return false;
			}

			const eventCategories = normalizeIds(event['ctx-event-categories']);
			if (categories.length > 0 && !categories.every((id) => eventCategories.includes(id))) {
				return false;
			}

			const eventTags = normalizeIds(event['ctx-event-tags']);
			if (tags.length > 0 && !tags.every((id) => eventTags.includes(id))) {
				return false;
			}

			return true;
		})
		.sort((a, b) => {
			const aDate = new Date(a.meta?._event_start || '').getTime();
			const bDate = new Date(b.meta?._event_start || '').getTime();
			return aDate - bDate;
		});

	return filtered[0] ?? null;
}

export function getEventFromContext(
	select: unknown,
	context?: Context,
): EventRecord | null {
	const selectionMode = context?.['ctx-events/selectionMode'] ?? 'manual';
	const selectedEventId = Number(context?.['ctx-events/eventId'] ?? 0);
	const fallbackEventId =
		context?.postType === 'ctx-event' ? Number(context?.postId ?? 0) : 0;

	if (selectionMode === 'query') {
		return getQueryEventFromContext(select, context);
	}

	const eventId =
		selectionMode === 'current'
			? fallbackEventId
			: selectedEventId || fallbackEventId;

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
