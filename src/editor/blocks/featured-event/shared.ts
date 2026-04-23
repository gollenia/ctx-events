import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

export type FeaturedEventContext = {
	postId?: number;
	postType?: string;
	'ctx-events/eventId'?: number;
};

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

export function stripHtml(html?: string): string {
	if (!html) return '';
	return html.replace(/<[^>]+>/g, '').trim();
}

export function useFeaturedEventData(context?: FeaturedEventContext) {
	const selectedEventId = Number(context?.['ctx-events/eventId'] ?? 0);
	const fallbackEventId =
		context?.postType === 'ctx-event' ? Number(context?.postId ?? 0) : 0;
	const eventId = selectedEventId || fallbackEventId;

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
