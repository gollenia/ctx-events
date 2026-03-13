import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';
import type { DataFilterElement } from '../../shared/datatable/types';

type FilterOptions = {
	gateways: Array<DataFilterElement<string>>;
};

export const useFilterOptions = (): FilterOptions => {
	const [gateways, setGateways] = useState<Array<DataFilterElement<string>>>([]);

	useEffect(() => {
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

	return { gateways };
};
