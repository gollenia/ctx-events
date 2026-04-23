import apiFetch from '@wordpress/api-fetch';
import { useEffect, useMemo, useState } from '@wordpress/element';
import type { DataFilterField } from '@events/datatable/Filter';
import type { CalendarEvent } from './types';

const getFilterValue = (
	filters: Array<DataFilterField>,
	field: string,
): DataFilterField['value'] | null =>
	filters.find((filter) => filter.field === field)?.value ?? null;

export const useFetchEventCalendar = (
	activeMonth: Date,
	filters: Array<DataFilterField>,
) => {
	const [events, setEvents] = useState<Array<CalendarEvent>>([]);
	const [loading, setLoading] = useState(false);

	const query = useMemo(() => {
		const startDate = new Date(
			activeMonth.getFullYear(),
			activeMonth.getMonth(),
			1,
		);
		const endDate = new Date(
			activeMonth.getFullYear(),
			activeMonth.getMonth() + 1,
			0,
			23,
			59,
			59,
		);

		const params = new URLSearchParams({
			start_date: startDate.toISOString(),
			end_date: endDate.toISOString(),
		});

		const categories = getFilterValue(filters, 'categories');
		if (Array.isArray(categories)) {
			for (const category of categories) {
				params.append('categories', String(category));
			}
		}

		const location = getFilterValue(filters, 'location');
		if (location) {
			params.append('location', String(location));
		}

		const person = getFilterValue(filters, 'persons');
		if (Array.isArray(person) && person.length > 0) {
			params.append('person', String(person[0]));
		}

		return params.toString();
	}, [activeMonth, filters]);

	useEffect(() => {
		const load = async () => {
			setLoading(true);
			try {
				const response = await apiFetch<Array<CalendarEvent>>({
					path: `/events/v3/events/calendar?${query}`,
				});
				setEvents(response);
			} catch (error) {
				console.error('Calendar fetch error', error);
			} finally {
				setLoading(false);
			}
		};

		load();
	}, [query]);

	return { events, loading };
};
