import { InputField } from '@contexis/wp-react-form';
import apiFetch from '@wordpress/api-fetch';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { mapEventsTableEvent } from './mappers';
import Table from './Table';
import type {
	EventsTableAttributes,
	EventsTableEvent,
	EventsTableTerm,
} from './types';

type Props = { attributes: EventsTableAttributes };

export default function EventsTable({ attributes }: Props) {
	const [events, setEvents] = useState<EventsTableEvent[]>([]);
	const [status, setStatus] = useState<'loading' | 'loaded' | 'error'>(
		'loading',
	);
	const [error, setError] = useState('');
	const [filter, setFilter] = useState({
		category: 0,
		tags: [] as number[],
		search: '',
	});
	const order = attributes.order === 'desc' ? 'desc' : 'asc';
	const showFilters =
		attributes.showCategoryFilter ||
		attributes.showTagFilter ||
		attributes.showSearch;

	useEffect(() => {
		const params = new URLSearchParams({
			include: 'location,categories,tags,bookings,persons',
			per_page: String(attributes.limit),
			order,
			scope: attributes.scope,
		});
		if (attributes.includeRecurring) params.set('with_recurrences', 'true');
		for (const id of attributes.selectedCategory)
			params.append('categories', String(id));
		for (const id of attributes.selectedTags) params.append('tags', String(id));
		if (attributes.selectedLocation)
			params.set('location', String(attributes.selectedLocation));

		setStatus('loading');
		apiFetch({ path: `/events/v3/events?${params.toString()}` })
			.then((response) => {
				setEvents(
					(response as Array<Parameters<typeof mapEventsTableEvent>[0]>).map(
						mapEventsTableEvent,
					),
				);
				setStatus('loaded');
			})
			.catch((fetchError: unknown) => {
				setError(
					fetchError instanceof Error
						? fetchError.message
						: __('Failed to load events', 'ctx-events'),
				);
				setStatus('error');
			});
	}, [
		attributes.includeRecurring,
		attributes.limit,
		attributes.scope,
		attributes.selectedCategory,
		attributes.selectedLocation,
		attributes.selectedTags,
		order,
	]);

	const categories = useMemo(
		() =>
			events.reduce<Record<number, EventsTableTerm>>((all, event) => {
				for (const category of event.categories) all[category.id] = category;
				return all;
			}, {}),
		[events],
	);
	const tags = useMemo(
		() =>
			events.reduce<Record<number, EventsTableTerm>>((all, event) => {
				for (const tag of event.tags) all[tag.id] = tag;
				return all;
			}, {}),
		[events],
	);
	const filteredEvents = useMemo(
		() =>
			events
				.filter((event) => {
					if (
						filter.category &&
						!event.categories.some(({ id }) => id === filter.category)
					)
						return false;
					if (
						filter.tags.length &&
						!filter.tags.some((id) => event.tags.some((tag) => tag.id === id))
					)
						return false;
					return (
						!filter.search ||
						event.title.toLowerCase().includes(filter.search.toLowerCase())
					);
				})
				.sort((a, b) =>
					order === 'asc'
						? new Date(a.start).getTime() - new Date(b.start).getTime()
						: new Date(b.start).getTime() - new Date(a.start).getTime(),
				),
		[events, filter, order],
	);
	const toggleTag = (tagId: number) =>
		setFilter((current) => ({
			...current,
			tags: current.tags.includes(tagId)
				? current.tags.filter((id) => id !== tagId)
				: [...current.tags, tagId],
		}));

	if (status === 'error')
		return (
			<div className="ctx-events-table__error">
				<h4>{__('An Error occurred', 'ctx-events')}</h4>
				{error}
			</div>
		);
	if (status === 'loaded' && events.length === 0)
		return <div>{attributes.altText}</div>;

	return (
		<div className="ctx-events-table-block__content">
			{showFilters && (
				<aside className="ctx-events-table-filters">
					{attributes.showSearch && (
						<InputField
							type="text"
							name="search"
							label={__('Search', 'ctx-events')}
							onChange={(search) =>
								setFilter((current) => ({ ...current, search }))
							}
							formTouched={false}
							status="LOADED"
							value={filter.search}
							disabled={status === 'loading'}
						/>
					)}
					{attributes.showCategoryFilter &&
						Object.keys(categories).length > 0 && (
							<div>
								<h5>{__('Select category', 'ctx-events')}</h5>
								<div className="ctx-events-table-filters__pills">
									<button
										type="button"
										className={filter.category === 0 ? 'active' : ''}
										onClick={() =>
											setFilter((current) => ({ ...current, category: 0 }))
										}
									>
										{__('All', 'ctx-events')}
									</button>
									{Object.values(categories).map((category) => (
										<button
											type="button"
											key={category.id}
											className={
												filter.category === category.id ? 'active' : ''
											}
											onClick={() =>
												setFilter((current) => ({
													...current,
													category: category.id,
												}))
											}
										>
											{category.name}
										</button>
									))}
								</div>
							</div>
						)}
					{attributes.showTagFilter && Object.keys(tags).length > 0 && (
						<div>
							<h5>{__('Select tags', 'ctx-events')}</h5>
							{Object.values(tags).map((tag) => (
								<label className="ctx-events-table-filters__tag" key={tag.id}>
									<input
										type="checkbox"
										checked={filter.tags.includes(tag.id)}
										onChange={() => toggleTag(tag.id)}
									/>
									{tag.name}
								</label>
							))}
						</div>
					)}
				</aside>
			)}
			<Table attributes={attributes} events={filteredEvents} />
		</div>
	);
}
