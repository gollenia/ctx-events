import PostTable from '@events/datatable/DataTable';
import type { DataFilterField, DataViewConfig } from '@events/datatable/types';

import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import { actions } from './actions';
import { eventStatusItems } from './eventStatusItems';
import { fields } from './fields';
import { useFetchEvents } from './useFetchEvents';

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

	const [view, setView] = useState<DataViewConfig>({
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
	});

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
		/>
	);
};

export default EventsList;
