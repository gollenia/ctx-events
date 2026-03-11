import DataTable from '@events/datatable/DataTable';
import type { DataViewConfig } from '@events/datatable/types';
import { useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { createActions } from './actions';
import { bookingStatusItems } from './bookingStatusItems';
import BookingEditModal from './edit/BookingEditModal';
import { createFields } from './fields';
import { useFetchBookings } from './useFetchBookings';
import { useFilterOptions } from './useFilterOptions';

const BookingsList = () => {
	const [view, setView] = useState<DataViewConfig>({
		search: '',
		page: 1,
		perPage: 25,
		sort: { field: 'date', direction: 'desc' },
		filters: [],
		titleField: 'name',
		fields: [
			'name',
			'event',
			'reference',
			'date',
			'spaces',
			'status',
			'email',
			'price',
			'gateway',
		],
	});

	const [refreshKey, setRefreshKey] = useState(0);
	const [editingReference, setEditingReference] = useState<string | null>(null);

	const filterOptions = useFilterOptions();

	const handleViewChange = (updates: Partial<DataViewConfig>) => {
		setView((prev) => {
			const next = { ...prev, ...updates };
			if (updates.filters !== undefined || updates.search !== undefined) {
				next.page = 1;
			}
			return next;
		});
	};

	const refresh = () => setRefreshKey((key) => key + 1);

	const fields = useMemo(
		() =>
			createFields(filterOptions, {
				onEventClick: (eventId) =>
					handleViewChange({
						filters: [{ field: 'event_id', operator: 'is', value: eventId }],
					}),
				onReferenceClick: setEditingReference,
			}),
		[filterOptions],
	);

	const { bookings, loading, statusItems, pagination } = useFetchBookings(view, refreshKey);

	const actions = useMemo(() => createActions(refresh), []);

	return (
		<>
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

			<BookingEditModal
				reference={editingReference}
				availableGateways={filterOptions.gateways}
				onClose={() => setEditingReference(null)}
				onSaved={refresh}
			/>
		</>
	);
};

export default BookingsList;
