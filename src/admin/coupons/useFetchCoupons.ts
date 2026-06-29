import type { DataViewConfig } from '@events/datatable';
import apiFetch from '@wordpress/api-fetch';
import { useEffect, useMemo, useState } from '@wordpress/element';
import type { CouponListItem } from 'src/types/types';

export const useFetchCoupons = (view: DataViewConfig) => {
	const [coupons, setCoupons] = useState<CouponListItem[]>([]);
	const [loading, setLoading] = useState(false);
	const [statusItems, setStatusItems] = useState<Record<string, number>>({});
	const [pagination, setPagination] = useState({
		totalItems: 0,
		totalPages: 0,
	});

	const urlParams = useMemo(() => {
		const params = new URLSearchParams({
			page: view.page?.toString() ?? '1',
			per_page: view.perPage?.toString() ?? '25',
			order_by: view.sort?.field ?? 'date',
			order: view.sort?.direction ?? 'desc',
			search: view.search || '',
			status:
				view.filters?.find((filter) => filter.field === 'status')?.value?.toString() ||
				'',
		});

		return params.toString();
	}, [view]);

	useEffect(() => {
		const loadData = async () => {
			setLoading(true);
			try {
				const response = (await apiFetch({
					path: `/events/v3/coupons?${urlParams}`,
					parse: false,
				})) as Response;

				const total = parseInt(response.headers.get('X-WP-Total') || '0', 10);
				const pages = parseInt(
					response.headers.get('X-WP-TotalPages') || '1',
					10,
				);
				const rawStatus = response.headers.get('X-WP-StatusCounts');

				setCoupons(await response.json());
				setPagination({ totalItems: total, totalPages: pages });

				if (rawStatus) {
					setStatusItems(JSON.parse(rawStatus));
				}
			} finally {
				setLoading(false);
			}
		};

		loadData();
	}, [urlParams, view.refreshKey]);

	return { coupons, loading, statusItems, pagination };
};
