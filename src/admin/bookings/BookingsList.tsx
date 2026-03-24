import DataTable from '@events/datatable/DataTable';
import type { DataFilterField, DataViewConfig } from '@events/datatable/types';
import apiFetch from '@wordpress/api-fetch';
import { SnackbarList } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { store as noticesStore } from '@wordpress/notices';
import { createActions } from './actions';
import { bookingStatusItems } from './bookingStatusItems';
import BookingEditModal from './edit/BookingEditModal';
import { createFields } from './fields';
import { useFetchBookings } from './useFetchBookings';
import { useFilterOptions } from './useFilterOptions';

const BookingsList = () => {
	const getActiveEventId = (filters: Array<DataFilterField>): string | null => {
		const eventFilter = filters.find((filter) => filter.field === 'event_id');
		if (
			eventFilter?.value === undefined ||
			eventFilter.value === null ||
			eventFilter.value === ''
		) {
			return null;
		}

		return String(eventFilter.value);
	};

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
	const { createWarningNotice, removeNotice } = useDispatch(noticesStore);
	const notices = useSelect((select) => select(noticesStore).getNotices(), []);

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
		const eventFilter = view.filters?.find(
			(filter) => filter.field === 'event_id',
		);

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

	const actions = useMemo(
		() =>
			createActions((warnings) => {
				warnings.forEach((warning) => {
					createWarningNotice(warning, { type: 'snackbar' });
				});
			}),
		[createWarningNotice],
	);
	const activeEventId = getActiveEventId(view.filters);

	const buildExportPath = (includeAttendees: boolean): string | null => {
		if (!activeEventId) {
			return null;
		}

		const searchParams = new URLSearchParams();
		searchParams.set('event_id', activeEventId);
		if (includeAttendees) {
			searchParams.set('include_attendees', '1');
		}

		return `/events/v3/bookings/export?${searchParams.toString()}`;
	};

	const handleExport = (includeAttendees: boolean) => {
		const path = buildExportPath(includeAttendees);
		if (!path) {
			return;
		}

		void apiFetch({
			path,
			method: 'GET',
			parse: false,
		})
			.then(async (response) => {
				if (!(response instanceof Response) || !response.ok) {
					let errorMessage = __('The export could not be downloaded.', 'ctx-events');

					if (response instanceof Response) {
						const contentType = response.headers.get('content-type') ?? '';

						if (contentType.includes('application/json')) {
							const payload = (await response.json()) as {
								message?: string;
								code?: string;
							};
							errorMessage = payload.message ?? payload.code ?? errorMessage;
						} else {
							const text = await response.text();
							if (text.trim() !== '') {
								errorMessage = text;
							}
						}
					}

					throw new Error(errorMessage);
				}

				const blob = await response.blob();
				const disposition = response.headers.get('content-disposition');
				const fileNameMatch = disposition?.match(/filename="?([^"]+)"?/i);
				const fileName = fileNameMatch?.[1] ?? 'bookings-export.xlsx';
				const objectUrl = window.URL.createObjectURL(blob);
				const anchor = document.createElement('a');
				anchor.href = objectUrl;
				anchor.download = fileName;
				document.body.append(anchor);
				anchor.click();
				anchor.remove();
				window.URL.revokeObjectURL(objectUrl);
			})
			.catch((error: unknown) => {
				const message = (() => {
					if (
						typeof error === 'object' &&
						error !== null &&
						'message' in error &&
						typeof error.message === 'string' &&
						error.message.trim() !== ''
					) {
						const code =
							'code' in error && typeof error.code === 'string'
								? error.code
								: null;
						const status =
							'data' in error &&
							typeof error.data === 'object' &&
							error.data !== null &&
							'status' in error.data &&
							typeof error.data.status === 'number'
								? error.data.status
								: null;

						if (code && status) {
							return `${error.message} (${code}, ${status})`;
						}

						if (code) {
							return `${error.message} (${code})`;
						}

						return error.message;
					}

					return __(
						'The export could not be downloaded. Please check your permissions and try again.',
						'ctx-events',
					);
				})();

				window.alert(
					message,
				);
			});
	};

	return (
		<>
			<SnackbarList notices={notices} onRemove={removeNotice} />
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
			>
				<DataTable.Header />
				<div style={{ display: 'flex', gap: '8px', marginBottom: '12px' }}>
					<button
						type="button"
						className="page-title-action"
						onClick={() => handleExport(false)}
						disabled={!activeEventId}
					>
						{__('Export Excel', 'ctx-events')}
					</button>
					<button
						type="button"
						className="page-title-action"
						onClick={() => handleExport(true)}
						disabled={!activeEventId}
					>
						{__('Export Excel + Attendees', 'ctx-events')}
					</button>
				</div>
				<DataTable.StatusSelect />
				<DataTable.Filter />
				<DataTable.Table />
				<DataTable.Pagination />
			</DataTable>

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
