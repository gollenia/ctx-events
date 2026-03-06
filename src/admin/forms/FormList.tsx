import DataTable from '@events/datatable/DataTable';
import type { DataFilterField, DataViewConfig } from '@events/datatable/types';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import actions from './actions';
import { fields } from './fields';
import { formStatusItems } from './formStatusItems';
import { useFetchForms } from './useFetchForms';

const FormList = () => {
	const [view, setView] = useState<DataViewConfig>({
		search: '',
		page: 1,
		perPage: 20,
		sort: { field: 'startDate', direction: 'asc' },
		filters: [
			{ field: 'type', operator: 'is', value: '' },
		] as Array<DataFilterField>,
		titleField: 'title',
		fields: ['title', 'type', 'used', 'date', 'description'] as Array<string>,
	});

	const { forms, loading, statusItems, pagination } = useFetchForms(view);

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
		<DataTable
			data={forms}
			fields={fields}
			view={view}
			actions={actions}
			search={true}
			onChangeView={handeViewChange}
			paginationInfo={pagination}
			isLoading={loading}
			searchLabel={__('Search Forms...', 'ctx-events')}
			availableStatusItems={formStatusItems(statusItems)}
			title={__('Forms', 'ctx-events')}
			createLink="/wp-admin/post-new.php?post_type=ctx-booking-form"
			createLinkLabel={__('New Form', 'ctx-events')}
		>
			<h1 className="wp-heading-inline">{__('Forms', 'ctx-events')}</h1>

			<a
				href="/wp-admin/post-new.php?post_type=ctx-booking-form"
				className="page-title-action"
			>
				{__('New Booking Form', 'ctx-events')}
			</a>

			<a
				href="/wp-admin/post-new.php?post_type=ctx-attendee-form"
				className="page-title-action"
			>
				{__('New Attendee Form', 'ctx-events')}
			</a>

			<hr className="wp-header-end" />

			<DataTable.StatusSelect />
			<DataTable.Filter />
			<DataTable.Table />
			<DataTable.Pagination />
		</DataTable>
	);
};

export default FormList;
