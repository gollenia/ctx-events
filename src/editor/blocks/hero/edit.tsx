import { useBlockProps } from '@wordpress/block-editor';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { formatDateRange } from '@events/i18n';
import Inspector from './inspector';

type HeroAttributes = {
	selectedEvent: number;
	showExcerpt: boolean;
	showLocation: boolean;
	showButton: boolean;
	buttonText: string;
	layout: 'split' | 'cover';
};

type EventOption = {
	label: string;
	value: number;
};

type EventRecord = {
	id: number;
	title?: { raw?: string; rendered?: string };
	excerpt?: { rendered?: string };
	meta?: {
		_event_start?: string;
		_event_end?: string;
		_location_id?: number;
	};
	link?: string;
	_embedded?: {
		'wp:featuredmedia'?: Array<{
			source_url?: string;
		}>;
	};
};

type LocationRecord = {
	title?: { raw?: string; rendered?: string };
};

type EditProps = {
	attributes: HeroAttributes;
	context?: {
		postId?: number;
		postType?: string;
	};
	setAttributes: (attributes: Partial<HeroAttributes>) => void;
};

function stripHtml(html?: string): string {
	if (!html) return '';
	return html.replace(/<[^>]+>/g, '').trim();
}

export default function Edit({ attributes, context, setAttributes }: EditProps) {
	const currentEventId =
		attributes.selectedEvent ||
		(context?.postType === 'ctx-event' ? (context.postId ?? 0) : 0);

	const eventOptions = useSelect((select) => {
		const list = ((select(coreStore) as {
			getEntityRecords: (
				kind: string,
				name: string,
				query?: Record<string, unknown>,
			) => EventRecord[] | null;
		}).getEntityRecords('postType', 'ctx-event', {
			per_page: -1,
			status: ['publish', 'future', 'draft', 'private'],
		}) ?? []) as EventRecord[];

		return list.map((event) => ({
			label:
				event.title?.raw || event.title?.rendered || `${__('Event', 'ctx-events')} #${event.id}`,
			value: event.id,
		})) as EventOption[];
	}, []);

	const event = useSelect(
		(select) =>
			currentEventId
				? ((select(coreStore) as {
						getEntityRecord: (
							kind: string,
							name: string,
							id: number,
							query?: Record<string, unknown>,
						) => EventRecord | null;
					}).getEntityRecord('postType', 'ctx-event', currentEventId, {
						_embed: true,
					}))
				: null,
		[currentEventId],
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

	const blockProps = useBlockProps({
		className: `ctx-event-hero is-layout-${attributes.layout}`,
	});

	const imageUrl = event?._embedded?.['wp:featuredmedia']?.[0]?.source_url;
	const eventTitle =
		event?.title?.raw || event?.title?.rendered || __('Select an event', 'ctx-events');
	const eventExcerpt = stripHtml(event?.excerpt?.rendered);
	const eventDate =
		event?.meta?._event_start && event?.meta?._event_end
			? formatDateRange(event.meta._event_start, event.meta._event_end)
			: '';
	const locationName = location?.title?.raw || location?.title?.rendered || '';

	return (
		<div {...blockProps}>
			<Inspector
				attributes={attributes}
				eventOptions={eventOptions}
				setAttributes={setAttributes}
			/>
			<div className="ctx-event-hero__media">
				{imageUrl ? (
					<img src={imageUrl} alt="" />
				) : (
					<div className="ctx-event-hero__media-placeholder">
						{__('No event image', 'ctx-events')}
					</div>
				)}
			</div>
			<div className="ctx-event-hero__content">
				<div className="ctx-event-hero__eyebrow">{eventDate}</div>
				<h2 className="ctx-event-hero__title">{eventTitle}</h2>
				{attributes.showLocation && locationName && (
					<div className="ctx-event-hero__meta">{locationName}</div>
				)}
				{attributes.showExcerpt && eventExcerpt && (
					<p className="ctx-event-hero__excerpt">{eventExcerpt}</p>
				)}
				{attributes.showButton && (
					<div className="ctx-event-hero__actions">
						<span className="ctx-event-hero__button">
							{attributes.buttonText || __('View event', 'ctx-events')}
						</span>
					</div>
				)}
			</div>
		</div>
	);
}
