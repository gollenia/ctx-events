import type { DataViewConfig } from '@events/datatable/types';
import apiFetch from '@wordpress/api-fetch';
import { useEffect, useMemo, useState } from '@wordpress/element';

export const useFetchBookings = (view: DataViewConfig, refreshKey: number) => {
	const [bookings, setBookings] = useState<object[]>([]);
	const [loading, setLoading] = useState(false);
	const [statusItems, setStatusItems] = useState<Record<string, number>>({});
	const [pagination, setPagination] = useState({ totalItems: 0, totalPages: 0 });

	const urlParams = useMemo(() => {
		const params = new URLSearchParams({
			page: String(view.page ?? 1),
			per_page: String(view.perPage ?? 25),
			order_by: view.sort?.field ?? 'date',
			order: view.sort?.direction ?? 'desc',
			search: view.search ?? '',
		});

		const statusFilter = view.filters?.find((f) => f.field === 'status');
		if (statusFilter?.value !== undefined && statusFilter.value !== '') {
			const values = Array.isArray(statusFilter.value)
				? statusFilter.value
				: [statusFilter.value];
			for (const value of values) {
				params.append('status[]', String(value));
			}
		}

		const eventFilter = view.filters?.find((f) => f.field === 'event_id');
		if (eventFilter?.value) {
			params.set('event_id', String(eventFilter.value));
		}

		const gatewayFilter = view.filters?.find((f) => f.field === 'gateway');
		if (gatewayFilter?.value) {
			params.set('gateway', String(gatewayFilter.value));
		}

		return params.toString();
	}, [view, refreshKey]);

	useEffect(() => {
		const loadData = async () => {
			setLoading(true);
			try {
				const response = (await apiFetch({
					path: `/events/v3/bookings?${urlParams}`,
					parse: false,
				})) as Response;

				const total = parseInt(response.headers.get('X-WP-Total') ?? '0', 10);
				const pages = parseInt(response.headers.get('X-WP-TotalPages') ?? '1', 10);
				const rawStatus = response.headers.get('X-WP-StatusCounts');

				setBookings(await response.json());
				setPagination({ totalItems: total, totalPages: pages });
				if (rawStatus) {
					setStatusItems(JSON.parse(rawStatus));
				}
			} catch (error) {
				console.error('Fetch bookings error:', error);
			} finally {
				setLoading(false);
			}
		};

		loadData();
	}, [urlParams]);

	return { bookings, loading, statusItems, pagination };
};
