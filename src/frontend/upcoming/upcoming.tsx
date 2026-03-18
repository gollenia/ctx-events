import apiFetch from '@wordpress/api-fetch';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import CardView from './CardView';
import ListView from './ListView';
import { mapUpcomingEvent } from './mappers';
import TableView from './Table';
import './style.scss';
import type { UpcomingAttributes, UpcomingTerm, UpcomingViewEvent } from './types';

type Props = {
	attributes: UpcomingAttributes;
};

function Upcoming({ attributes }: Props) {
	const {
		userStylePicker,
		view,
		limit,
		order,
		selectedCategory,
		selectedLocation,
		selectedTags,
		scope,
		filterPosition,
		altText,
		showCategoryFilter,
		showTagFilter,
		showSearch,
		showPerson,
	} = attributes;
	const personView = showPerson || '';

	const [events, setEvents] = useState<UpcomingViewEvent[]>([]);
	const [status, setStatus] = useState<'LOADING' | 'LOADED' | 'ERROR'>('LOADING');
	const [filterMobileVisible, setFilterMobileVisible] = useState(false);
	const [customView, setCustomView] = useState<'cards' | 'list' | 'mini' | ''>('');
	const [filter, setFilter] = useState<{
		category: number;
		tags: number[];
		string: string;
	}>({
		category: 0,
		tags: [],
		string: '',
	});
	const [error, setError] = useState('');

	const categories = useMemo(() => {
		return events.reduce<Record<number, UpcomingTerm>>((acc, event) => {
			for (const category of event.categories) {
				acc[category.id] = category;
			}
			return acc;
		}, {});
	}, [events]);

	const tags = useMemo(() => {
		return events.reduce<Record<number, UpcomingTerm>>((acc, event) => {
			for (const tag of event.tags) {
				acc[tag.id] = tag;
			}
			return acc;
		}, {});
	}, [events]);

	useEffect(() => {
		const params = new URLSearchParams({
			include: 'location,image,categories,tags,bookings,persons',
			per_page: String(limit),
			order,
			scope,
		});

		for (const categoryId of selectedCategory) {
			params.append('categories', String(categoryId));
		}

		for (const tagId of selectedTags) {
			params.append('tags', String(tagId));
		}

		if (selectedLocation) {
			params.set('location', String(selectedLocation));
		}

		apiFetch({
			path: `/events/v3/events?${params.toString()}`,
		})
			.then((response) => {
				const items = (response as Event[]).map(mapUpcomingEvent);
				setEvents(items);
				setStatus('LOADED');
			})
			.catch((fetchError: unknown) => {
				setStatus('ERROR');
				setError(
					fetchError instanceof Error
						? fetchError.message
						: __('Failed to load events', 'ctx-events'),
				);
			});
	}, [limit, order, selectedCategory, selectedLocation, selectedTags, scope]);

	const changeFilter = (
		key: 'category' | 'tags' | 'string',
		value: number | number[] | string,
	) => {
		setFilter((current) => ({ ...current, [key]: value }));
	};

	const toggleTag = (tagId: number) => {
		if (filter.tags.includes(tagId)) {
			changeFilter(
				'tags',
				filter.tags.filter((item) => item !== tagId),
			);
			return;
		}

		changeFilter('tags', [...filter.tags, tagId]);
	};

	const getFilteredEvents = () => {
		let filtered = [...events];

		if (filter.category !== 0) {
			filtered = filtered.filter((item) =>
				item.categories.some((category) => category.id === filter.category),
			);
		}

		if (filter.string !== '') {
			filtered = filtered.filter((item) =>
				item.title.toLowerCase().includes(filter.string.toLowerCase()),
			);
		}

		if (filter.tags.length > 0) {
			filtered = filtered.filter((item) =>
				filter.tags.some((tagId) => item.tags.some((tag) => tag.id === tagId)),
			);
		}

		filtered.sort((a, b) =>
			order === 'ASC'
				? new Date(a.start).getTime() - new Date(b.start).getTime()
				: new Date(b.start).getTime() - new Date(a.start).getTime(),
		);

		return filtered;
	};

	const currentView = customView || view;
	const showFilters = showCategoryFilter || showTagFilter || showSearch;

	if (status === 'ERROR') {
		return (
			<div className="error">
				<h4>{__('An Error occurred', 'ctx-events')}</h4>
				{error}
			</div>
		);
	}

	if (events.length === 0 && status === 'LOADED') {
		return <div>{altText}</div>;
	}

	return (
		<div
			className={`upcoming__events ${showFilters ? 'has-filters' : ''} event-filters-${filterPosition}`}
		>
			{showFilters && (
				<aside className="event-filters">
					<div className="event-filters-header">
						{showSearch && (
							<div className="filter__search">
								<div className="input">
									<label>{__('Search', 'ctx-events')}</label>
									<input
										type="text"
										onChange={(event) => {
											changeFilter('string', event.target.value);
										}}
									/>
								</div>
							</div>
						)}

						{userStylePicker && (
							<div className="view-switcher">
								<button
									type="button"
									onClick={() => setCustomView('cards')}
									className={currentView === 'cards' ? 'button active' : 'button'}
								>
									<i className="material-icons material-symbols-outlined">
										grid_view
									</i>
								</button>
								<button
									type="button"
									onClick={() => setCustomView('list')}
									className={currentView === 'list' ? 'button active' : 'button'}
								>
									<i className="material-icons material-symbols-outlined">
										view_agenda
									</i>
								</button>
								<button
									type="button"
									onClick={() => setCustomView('mini')}
									className={currentView === 'mini' ? 'button active' : 'button'}
								>
									<i className="material-icons material-symbols-outlined">
										format_list_bulleted
									</i>
								</button>
							</div>
						)}

						<div className="event-filter-toggle">
							<button
								type="button"
								className="button"
								onClick={() => setFilterMobileVisible(!filterMobileVisible)}
							>
								<i className="material-icons material-symbols-outlined">
									filter_list
								</i>
							</button>
						</div>
					</div>

					<div
						className={
							filterMobileVisible
								? 'event-filters-advanced'
								: 'event-filters-advanced mobile-hidden'
						}
					>
						{showCategoryFilter && Object.keys(categories).length > 0 && (
							<div>
								<h5 className="event-filters-title">
									{__('Select category', 'ctx-events')}
								</h5>
								<div className="event-filter-pills">
									<button
										type="button"
										className={filter.category === 0 ? 'active' : ''}
										onClick={() => {
											changeFilter('category', 0);
										}}
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
											onClick={() => {
												changeFilter('category', category.id);
											}}
										>
											{category.name}
										</button>
									))}
								</div>
							</div>
						)}

						{showTagFilter && Object.keys(tags).length > 0 && (
							<div>
								<h5 className="event-filters-title">
									{__('Select tags', 'ctx-events')}
								</h5>
								{Object.values(tags).map((tag) => (
									<div className="filter__box checkbox" key={tag.id}>
										<label>
											<input
												type="checkbox"
												name={String(tag.id)}
												onChange={() => toggleTag(tag.id)}
												checked={filter.tags.includes(tag.id)}
											/>
											{tag.name}
										</label>
									</div>
								))}
							</div>
						)}
					</div>
				</aside>
			)}

			<>
				{currentView === 'cards' && (
					<CardView
						attributes={{ ...attributes, showPerson: personView }}
						events={getFilteredEvents()}
						status={status}
					/>
				)}
				{currentView === 'list' && (
					<ListView
						attributes={{ ...attributes, showPerson: personView }}
						events={getFilteredEvents()}
						status={status}
					/>
				)}
				{currentView === 'mini' && (
					<TableView
						attributes={{ ...attributes, showPerson: personView }}
						events={getFilteredEvents()}
						status={status}
					/>
				)}
			</>
		</div>
	);
}

export default Upcoming;
