import PostTable from '@events/datatable/DataTable';
import type { DataFilterField, DataViewConfig } from '@events/datatable/types';

import { __ } from '@wordpress/i18n';
import type { TimeScope } from '../../types/types';

import { actions } from './actions';
import EventCalendarView from './calendar';
import { eventStatusItems } from './eventStatusItems';
import { fields } from './fields';
import { useFetchEvents } from './useFetchEvents';
import { useStoredView } from './useStoredView';

const getActiveScope = (filters: Array<DataFilterField>): TimeScope => {
	const dateFilter = filters.find((filter) => filter.field === 'date');
	return (dateFilter?.value as TimeScope | undefined) ?? 'future';
};

const EventsList = () => {
	const filterConfig: Array<DataFilterField> = [
		{
			field: 'date',
			operator: 'is',
			value: 'future',
		},
		{
			field: 'status',
			operator: 'is',
			value: 'publish',
		},
	];

	const defaultView: DataViewConfig = {
		search: '',
		page: 1,
		perPage: 20,
		sort: { field: 'startDate', direction: 'asc' },
		filters: filterConfig,
		titleField: 'title',
		fields: [
			'title',
			'date',
			'location',
			'tags',
			'categories',
			'price',
			'bookable',
			'availability',
		],
	};
	const { view, setView } = useStoredView(
		'ctx-events:admin:events:view',
		defaultView,
	);

	console.log('EventsList view', view);

	const { events, loading, statusItems, pagination } = useFetchEvents(view);

	const handeViewChange = (updates: Partial<DataViewConfig>) => {
		setView((prev) => {
			const nextView = { ...prev, ...updates };
			if (updates.filters || updates.search !== undefined) {
				nextView.page = 1;
			}
			return nextView;
		});
	};

	return (
		<PostTable
			data={events}
			fields={fields}
			view={view}
			actions={actions}
			search={true}
			onChangeView={handeViewChange}
			paginationInfo={pagination}
			isLoading={loading}
			searchLabel={__('Search Events...', 'ctx-events')}
			availableStatusItems={eventStatusItems(statusItems)}
			title={__('Events', 'ctx-events')}
			createLink="/wp-admin/post-new.php?post_type=ctx-event"
			createLinkLabel={__('New Event', 'ctx-events')}
			views={[{ id: 'calender', label: __('Calendar', 'ctx-events') }]}
		>
			<PostTable.Header />
			<PostTable.StatusSelect />
			{view.type === 'calender' ? (
				<EventCalendarView
					filters={view.filters}
					scope={getActiveScope(view.filters)}
				/>
			) : (
				<>
					<PostTable.Filter />
					<PostTable.Table />
				</>
			)}
			<PostTable.Pagination />
		</PostTable>
	);
};

export default EventsList;
