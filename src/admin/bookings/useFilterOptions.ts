import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';
import type { DataFilterElement } from '../../shared/datatable/types';

type FilterOptions = {
	events: Array<DataFilterElement<string>>;
	gateways: Array<DataFilterElement<string>>;
};

export const useFilterOptions = (): FilterOptions => {
	const [events, setEvents] = useState<Array<DataFilterElement<string>>>([]);
	const [gateways, setGateways] = useState<Array<DataFilterElement<string>>>([]);

	useEffect(() => {
		apiFetch({ path: '/events/v3/events?per_page=100&order_by=title&order=asc' })
			.then((data: any) => {
				const items = Array.isArray(data) ? data : [];
				setEvents(
					items.map((event: { id: number; name: string }) => ({
						value: String(event.id),
						label: event.name,
					})),
				);
			})
			.catch(() => {});

		apiFetch({ path: '/events/v3/gateways' })
			.then((data: any) => {
				const items = Array.isArray(data) ? data : [];
				setGateways(
					items.map((gateway: { slug: string; title: string }) => ({
						value: gateway.slug,
						label: gateway.title,
					})),
				);
			})
			.catch(() => {});
	}, []);

	return { events, gateways };
};
