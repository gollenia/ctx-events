import apiFetch from '@wordpress/api-fetch';
import {
	BaseControl,
	Button,
	Notice,
	Spinner,
	TextControl,
} from '@wordpress/components';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type {
	FeaturedEventAttributes,
	FeaturedEventContext,
} from './types';

type EventListItem = {
	id: number;
	name: string;
	startDate: string;
	status: string;
	includes?: {
		location?: {
			name?: string;
		};
	};
};

type Props = {
	attributes: FeaturedEventAttributes;
	context?: FeaturedEventContext;
	label?: string;
	onChange: (attributes: Partial<FeaturedEventAttributes>) => void;
};

function formatEventMeta(event: EventListItem): string {
	const parts: string[] = [];

	if (event.startDate) {
		const date = new Date(event.startDate);
		if (!Number.isNaN(date.getTime())) {
			parts.push(
				new Intl.DateTimeFormat(undefined, {
					dateStyle: 'medium',
					timeStyle: 'short',
				}).format(date),
			);
		}
	}

	if (event.includes?.location?.name) {
		parts.push(event.includes.location.name);
	}

	return parts.join(' | ');
}

export default function EventSelector({
	attributes,
	context,
	label,
	onChange,
}: Props) {
	const [search, setSearch] = useState('');
	const [results, setResults] = useState<EventListItem[]>([]);
	const [selectedEvent, setSelectedEvent] = useState<EventListItem | null>(null);
	const [isSearching, setIsSearching] = useState(false);
	const [isLoadingSelected, setIsLoadingSelected] = useState(false);
	const [error, setError] = useState('');
	const selectedEventId = attributes.selectedEvent ?? 0;

	useEffect(() => {
		let isCancelled = false;

		if (selectedEventId <= 0) {
			setSelectedEvent(null);
			return () => {
				isCancelled = true;
			};
		}

		setIsLoadingSelected(true);
		setError('');

		apiFetch<EventListItem>({
			path: `/events/v3/events/${selectedEventId}?include=location`,
		})
			.then((event) => {
				if (!isCancelled) {
					setSelectedEvent(event);
				}
			})
			.catch(() => {
				if (!isCancelled) {
					setSelectedEvent(null);
					setError(__('The selected event could not be loaded.', 'ctx-events'));
				}
			})
			.finally(() => {
				if (!isCancelled) {
					setIsLoadingSelected(false);
				}
			});

		return () => {
			isCancelled = true;
		};
	}, [selectedEventId]);

	useEffect(() => {
		let isCancelled = false;

		if (search.trim().length < 2) {
			setResults([]);
			setIsSearching(false);
			return () => {
				isCancelled = true;
			};
		}

		const timer = window.setTimeout(() => {
			setIsSearching(true);
			setError('');
			apiFetch<EventListItem[]>({
				path: `/events/v3/events?per_page=12&scope=all&search=${encodeURIComponent(
					search.trim(),
				)}&include=location`,
			})
				.then((events) => {
					if (!isCancelled) {
						setResults(Array.isArray(events) ? events : []);
					}
				})
				.catch(() => {
					if (!isCancelled) {
						setResults([]);
						setError(__('The event search failed.', 'ctx-events'));
					}
				})
				.finally(() => {
					if (!isCancelled) {
						setIsSearching(false);
					}
				});
		}, 250);

		return () => {
			isCancelled = true;
			window.clearTimeout(timer);
		};
	}, [search]);

	const selectedLabel = useMemo(() => {
		if (selectedEvent) {
			return selectedEvent.name;
		}

		if (context?.postType === 'ctx-event') {
			return __('Current event', 'ctx-events');
		}

		return __('No event selected yet.', 'ctx-events');
	}, [context?.postType, selectedEvent]);

	return (
		<BaseControl
			label={label || __('Selected event', 'ctx-events')}
			help={__(
				'Search by title. Matching events include date and location context.',
				'ctx-events',
			)}
		>
			<div className="ctx-featured-event-selector">
				<div className="ctx-featured-event-selector__current">
					<strong>{selectedLabel}</strong>
					{selectedEvent ? (
						<div>{formatEventMeta(selectedEvent)}</div>
					) : null}
					{isLoadingSelected ? <Spinner /> : null}
				</div>

				<TextControl
					label={__('Search event', 'ctx-events')}
					value={search}
					onChange={setSearch}
					placeholder={__('Type at least 2 characters...', 'ctx-events')}
					__next40pxDefaultSize
				/>

				{context?.postType === 'ctx-event' ? (
					<Button
						variant="secondary"
						onClick={() => onChange({ selectedEvent: 0 })}
					>
						{__('Use current event', 'ctx-events')}
					</Button>
				) : null}

				{error ? (
					<Notice status="warning" isDismissible={false}>
						{error}
					</Notice>
				) : null}

				{isSearching ? <Spinner /> : null}

				{search.trim().length >= 2 && !isSearching ? (
					<div className="ctx-featured-event-selector__results">
						{results.length > 0 ? (
							results.map((event) => (
								<Button
									key={event.id}
									variant={
										event.id === selectedEventId ? 'primary' : 'tertiary'
									}
									onClick={() =>
										onChange({
											selectedEvent: event.id,
											selectionMode: 'manual',
										})
									}
								>
									{event.name}
									{formatEventMeta(event) ? ` (${formatEventMeta(event)})` : ''}
								</Button>
							))
						) : (
							<div>{__('No matching events found.', 'ctx-events')}</div>
						)}
					</div>
				) : null}
			</div>
		</BaseControl>
	);
}
