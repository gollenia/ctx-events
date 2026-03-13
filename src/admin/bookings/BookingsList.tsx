import DataTable from '@events/datatable/DataTable';
import type { DataFilterField, DataViewConfig } from '@events/datatable/types';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { createActions } from './actions';
import { bookingStatusItems } from './bookingStatusItems';
import BookingEditModal from './edit/BookingEditModal';
import { createFields } from './fields';
import { useFetchBookings } from './useFetchBookings';
import { useFilterOptions } from './useFilterOptions';

const BookingsList = () => {
	const getInitialFilters = (): Array<DataFilterField> => {
		const params = new URLSearchParams(window.location.search);
		const eventId = params.get('event_id');
		const filters: Array<DataFilterField> = [
			{
				field: 'status',
				operator: 'is',
				value: 1,
			},
		];

		if (eventId && eventId !== '') {
			filters.push({
				field: 'event_id',
				operator: 'is',
				value: eventId,
			});
		}

		return filters;
	};

	const [view, setView] = useState<DataViewConfig>({
		search: '',
		page: 1,
		perPage: 25,
		sort: { field: 'date', direction: 'desc' },
		filters: getInitialFilters(),
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

	useEffect(() => {
		const params = new URLSearchParams(window.location.search);
		const eventFilter = view.filters?.find((filter) => filter.field === 'event_id');

		if (eventFilter?.value) {
			params.set('event_id', String(eventFilter.value));
		} else {
			params.delete('event_id');
		}

		const nextQuery = params.toString();
		const nextUrl = `${window.location.pathname}${nextQuery ? `?${nextQuery}` : ''}`;
		window.history.replaceState({}, '', nextUrl);
	}, [view.filters]);

	const refresh = () =>
		setView((prev) => ({
			...prev,
			refreshKey: (prev.refreshKey ?? 0) + 1,
		}));

	const fields = useMemo(
		() =>
			createFields(filterOptions, {
				onReferenceClick: setEditingReference,
			}),
		[filterOptions],
	);

	const { bookings, loading, statusItems, pagination } = useFetchBookings(view);

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
