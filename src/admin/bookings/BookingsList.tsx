import DataTable from '@events/datatable/DataTable';
import type { DataViewConfig } from '@events/datatable/types';
import { useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { createActions } from './actions';
import { bookingStatusItems } from './bookingStatusItems';
import { createFields } from './fields';
import { useFilterOptions } from './useFilterOptions';
import { useFetchBookings } from './useFetchBookings';

const BookingsList = () => {
	const [view, setView] = useState<DataViewConfig>({
		search: '',
		page: 1,
		perPage: 25,
		sort: { field: 'date', direction: 'desc' },
		filters: [],
		titleField: 'name',
		fields: ['name', 'event', 'date', 'spaces', 'status', 'email', 'price', 'gateway'],
	});

	const [refreshKey, setRefreshKey] = useState(0);

	const filterOptions = useFilterOptions();
	const fields = useMemo(() => createFields(filterOptions), [filterOptions]);

	const { bookings, loading, statusItems, pagination } = useFetchBookings(view, refreshKey);

	const actions = useMemo(
		() => createActions(() => setRefreshKey((key) => key + 1)),
		[],
	);

	const handleViewChange = (updates: Partial<DataViewConfig>) => {
		setView((prev) => {
			const next = { ...prev, ...updates };
			if (updates.filters !== undefined || updates.search !== undefined) {
				next.page = 1;
			}
			return next;
		});
	};

	return (
		<DataTable
			data={bookings}
			fields={fields}
			view={view}
			actions={actions}
			search={true}
			onChangeView={handleViewChange}
			paginationInfo={pagination}
			isLoading={loading}
			searchLabel={__('Search Bookings…', 'ctx-events')}
			availableStatusItems={bookingStatusItems(statusItems)}
			title={__('Bookings', 'ctx-events')}
		/>
	);
};

export default BookingsList;
