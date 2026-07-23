import apiFetch from '@wordpress/api-fetch';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type { FeaturedEventContext } from './types';

type EventRecord = {
	id: number;
	title?: { raw?: string; rendered?: string };
	excerpt?: { rendered?: string };
	link?: string;
	meta?: {
		_event_start?: string;
		_event_end?: string;
		_location_id?: number;
	};
	_embedded?: {
		'wp:featuredmedia'?: Array<{
			source_url?: string;
			alt_text?: string;
		}>;
	};
};

type LocationRecord = {
	title?: { raw?: string; rendered?: string };
};

type QueryEventRecord = {
	id: number;
	name: string;
	description?: string | null;
	url?: string | null;
	startDate: string;
	endDate?: string | null;
	includes?: {
		image?: {
			url?: string | null;
			alt_text?: string | null;
		};
		location?: {
			name?: string;
		};
	};
};

const queryCache = new Map<string, QueryEventRecord | null>();

export function stripHtml(html?: string): string {
	if (!html) return '';
	return html.replace(/<[^>]+>/g, '').trim();
}

function getSelectionMode(context?: FeaturedEventContext) {
	return context?.['ctx-events/selectionMode'] ?? 'manual';
}

function getDirectEventId(context?: FeaturedEventContext): number {
	const selectionMode = getSelectionMode(context);
	const currentEventId =
		context?.postType === 'ctx-event' ? Number(context?.postId ?? 0) : 0;
	const selectedEventId = Number(context?.['ctx-events/eventId'] ?? 0);

	if (selectionMode === 'current') {
		return currentEventId;
	}

	if (selectionMode === 'manual') {
		return selectedEventId || currentEventId;
	}

	return 0;
}

function buildQueryPath(context?: FeaturedEventContext): string | null {
	if (getSelectionMode(context) !== 'query') {
		return null;
	}

	const params = new URLSearchParams();
	params.set('per_page', '1');
	params.set('order', 'ASC');
	params.set('scope', context?.['ctx-events/queryScope'] || 'future');
	params.append('include', 'location');
	params.append('include', 'image');

	for (const categoryId of context?.['ctx-events/queryCategoryIds'] ?? []) {
		params.append('categories', String(categoryId));
	}

	for (const tagId of context?.['ctx-events/queryTagIds'] ?? []) {
		params.append('tags', String(tagId));
	}

	const locationId = Number(context?.['ctx-events/queryLocationId'] ?? 0);
	if (locationId > 0) {
		params.set('location', String(locationId));
	}

	return `/events/v3/events?${params.toString()}`;
}

function useResolvedQueryEvent(context?: FeaturedEventContext) {
	const path = useMemo(
		() => buildQueryPath(context),
		[
			context?.['ctx-events/selectionMode'],
			context?.['ctx-events/queryScope'],
			context?.['ctx-events/queryLocationId'],
			JSON.stringify(context?.['ctx-events/queryCategoryIds'] ?? []),
			JSON.stringify(context?.['ctx-events/queryTagIds'] ?? []),
		],
	);
	const [event, setEvent] = useState<QueryEventRecord | null | undefined>(() => {
		if (!path) {
			return undefined;
		}

		return queryCache.get(path);
	});

	useEffect(() => {
		let isCancelled = false;

		if (!path) {
			setEvent(undefined);
			return () => {
				isCancelled = true;
			};
		}

		if (queryCache.has(path)) {
			setEvent(queryCache.get(path));
			return () => {
				isCancelled = true;
			};
		}

		setEvent(undefined);

		apiFetch<QueryEventRecord[]>({ path })
			.then((result) => {
				const first = Array.isArray(result) ? result[0] ?? null : null;
				queryCache.set(path, first);
				if (!isCancelled) {
					setEvent(first);
				}
			})
			.catch(() => {
				queryCache.set(path, null);
				if (!isCancelled) {
					setEvent(null);
				}
			});

		return () => {
			isCancelled = true;
		};
	}, [path]);

	return event;
}

export function useFeaturedEventData(context?: FeaturedEventContext) {
	const eventId = getDirectEventId(context);
	const queryEvent = useResolvedQueryEvent(context);
	const selectionMode = getSelectionMode(context);

	const event = useSelect(
		(select) =>
			eventId
				? ((select(coreStore) as {
						getEntityRecord: (
							kind: string,
							name: string,
							id: number,
							query?: Record<string, unknown>,
						) => EventRecord | null;
					}).getEntityRecord('postType', 'ctx-event', eventId, {
						_embed: true,
					}))
				: null,
		[eventId],
	);

	const location = useSelect(
		(select) => {
			const locationId = event?.meta?._location_id ?? 0;
			if (!locationId) return null;
			return (select(coreStore) as {
				getEntityRecord: (
					kind: string,
					name: string,
					id: number,
				) => LocationRecord | null;
			}).getEntityRecord('postType', 'ctx-event-location', locationId);
		},
		[event?.meta?._location_id],
	);

	if (selectionMode === 'query') {
		const title = queryEvent?.name || __('Select an event', 'ctx-events');

		return {
			eventId: queryEvent?.id ?? 0,
			event: null,
			title,
			excerpt: stripHtml(queryEvent?.description ?? ''),
			imageUrl: queryEvent?.includes?.image?.url ?? '',
			imageAlt: queryEvent?.includes?.image?.alt_text || title,
			locationName: queryEvent?.includes?.location?.name ?? '',
			link: queryEvent?.url ?? '',
			start: queryEvent?.startDate ?? '',
			end: queryEvent?.endDate ?? '',
		};
	}

	const title =
		event?.title?.raw || event?.title?.rendered || __('Select an event', 'ctx-events');
	const excerpt = stripHtml(event?.excerpt?.rendered);
	const image = event?._embedded?.['wp:featuredmedia']?.[0];
	const imageUrl = image?.source_url ?? '';
	const imageAlt = image?.alt_text || title;
	const locationName = location?.title?.raw || location?.title?.rendered || '';

	return {
		eventId,
		event,
		title,
		excerpt,
		imageUrl,
		imageAlt,
		locationName,
		link: event?.link ?? '',
		start: event?.meta?._event_start ?? '',
		end: event?.meta?._event_end ?? '',
	};
}

export function useResolvedFeaturedEventId(context?: FeaturedEventContext): number {
	return useFeaturedEventData(context).eventId;
}
