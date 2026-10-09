import DataTable from '@events/datatable/DataTable';
import type { DataViewConfig } from '@events/datatable/types';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { recurringEventFields } from './fields';
import { useFetchRecurringEvents } from './useFetchRecurringEvents';

const RecurringEventsPage = () => {
	const [view, setView] = useState<DataViewConfig>({ search: '', page: 1, perPage: 20, sort: { field: 'firstStartsAt', direction: 'asc' }, filters: [], titleField: 'title', fields: ['title', 'firstStartsAt', 'frequency', 'endsOn'] });
	const { items, isLoading, pagination } = useFetchRecurringEvents(view);
	const updateView = (updates: Partial<DataViewConfig>): void => setView((currentView) => ({ ...currentView, ...updates, page: updates.search !== undefined || updates.filters ? 1 : currentView.page }));

	return <DataTable data={items} fields={recurringEventFields} view={view} onChangeView={updateView} isLoading={isLoading} paginationInfo={pagination} search searchLabel={__('Search recurring events...', 'ctx-events')} availableStatusItems={[]} title={__('Recurring Events', 'ctx-events')} createLink="/wp-admin/post-new.php?post_type=ctx-event-recurring" createLinkLabel={__('New Recurring Event', 'ctx-events')}><DataTable.Header /><DataTable.Filter /><DataTable.Table /><DataTable.Pagination /></DataTable>;
};

export default RecurringEventsPage;
