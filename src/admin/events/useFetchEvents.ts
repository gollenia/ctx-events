import type { DataViewConfig } from '@events/datatable';
import type { DataFilterField } from '@events/datatable/Filter';
import apiFetch from '@wordpress/api-fetch';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { fields } from './fields';

type EventFilterField = DataFilterField & {
	id: string;
};

export const useFetchEvents = (view: DataViewConfig) => {
	const [events, setEvents] = useState<Event[]>([]);
	const [loading, setLoading] = useState(false);
	const [statusItems, setStatusItems] = useState<Record<string, number>>({});
	const [pagination, setPagination] = useState({
		totalItems: 0,
		totalPages: 0,
	});
	const markDetached = (occurrenceId: string, eventId: number): void => {
		setEvents((currentEvents) => currentEvents.map((event) => event.id === occurrenceId
			? { ...event, type: 'detached', eventId, bookingSummary: null }
			: event));
	};
	const removeOccurrence = (occurrenceId: string): void => {
		setEvents((currentEvents) => currentEvents.filter((event) => event.id !== occurrenceId));
	};

	const availableFilters: Array<EventFilterField> = useMemo(() => {
		return (
			view.filters
				?.map((filter) => {
					const fieldConfig = fields.find((f) => f.id === filter.field);
					if (fieldConfig?.filterBy) {
						return {
							...filter,
							id: fieldConfig.filterBy.id,
						};
					}
					return null;
				})
				.filter((f): f is EventFilterField => f !== null) || []
		);
	}, [view.filters]);

	const urlParams = useMemo(() => {
		const params = new URLSearchParams({
			include: 'location,tags,categories,bookings',
			page: view.page?.toString(),
			per_page: view.perPage?.toString(),
			order_by: view.sort?.field,
			order: view.sort?.direction,
			search: view.search || '',
		});

		for (const f of availableFilters) {
			params.append(f.id, String(f.value));
		}

		console.log('URL Params:', params.toString());

		if (view.search) params.append('search', view.search);
		return params.toString();
	}, [view, availableFilters]);

	useEffect(() => {
		const loadData = async () => {
			setLoading(true);
			try {
				const response = (await apiFetch({ path: `/events/v3/events?${urlParams}`, parse: false })) as Response;
				if (!view.showRecurrences) {
					setEvents(await response.json());
				} else {
					const recurringResponse = (await apiFetch({
						path: `/events/v3/events?${urlParams}&with_recurrences=true`,
						parse: false,
					})) as Response;
					const [events, occurrences] = await Promise.all([response.json(), recurringResponse.json()]) as [Event[], Event[]];
					const virtualOccurrences = occurrences.filter((event) => event.type === 'virtual');
					setEvents([...events, ...virtualOccurrences].sort((left, right) => new Date(left.startDate).getTime() - new Date(right.startDate).getTime()));
				}

				const total = parseInt(response.headers.get('X-WP-Total') || '0', 10);
				const pages = parseInt(
					response.headers.get('X-WP-TotalPages') || '1',
					10,
				);
				const rawStatus = response.headers.get('X-WP-StatusCounts');

				setPagination({ totalItems: total, totalPages: pages });
				if (rawStatus) setStatusItems(JSON.parse(rawStatus));
			} catch (error) {
				console.error('Fetch Error:', error);
			} finally {
				setLoading(false);
			}
		};

		loadData();
	}, [urlParams]);

	return { events, loading, statusItems, pagination, markDetached, removeOccurrence };
};
