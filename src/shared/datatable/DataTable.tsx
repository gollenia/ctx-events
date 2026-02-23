import type React from '@wordpress/element';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import Filter from './Filter';
import ScreenMeta from './ScreenMeta';
import ScreenMetaLinks from './ScreenMetaLinks';
import StatusSelect from './StatusSelect';
import Table from './Table';
import type {
	DataFieldConfig,
	DataPaginationInfo,
	DataStatusItem,
	DataTableAction,
	DataViewConfig,
} from './types';

interface DataTableProps {
	data: Array<any>;
	fields: Array<DataFieldConfig>;
	view: DataViewConfig;
	onChangeView: (updates: Partial<DataViewConfig>) => void;
	actions?: Array<DataTableAction>;
	paginationInfo?: DataPaginationInfo;
	search?: boolean;
	searchLabel?: string;
	isLoading: boolean;
	empty?: React.ComponentType;
	availableStatusItems: Array<DataStatusItem>;
	title?: string;
	createLink?: string;
	createLinkLabel?: string;
}

const DataTable = ({
	data,
	fields,
	view,
	onChangeView,
	actions,
	paginationInfo,
	searchLabel,
	isLoading,
	empty,
	availableStatusItems,
	title,
	createLink,
	createLinkLabel,
}: DataTableProps) => {
	const [screenMetaContext, setScreenMetaContext] = useState('');
	return (
		<>
			<ScreenMeta
				context={screenMetaContext}
				view={view}
				onChangeView={onChangeView}
				fields={fields}
			/>
			<ScreenMetaLinks
				setScreenMeta={(context) =>
					setScreenMetaContext(screenMetaContext ? '' : context)
				}
			/>
			<div
				className="wrap ctx-datatable"
				style={{ padding: '10px 20px 0 2px', margin: 0 }}
			>
				<h1 className="wp-heading-inline">
					{title || __('Items', 'ctx-events')}
				</h1>
				{createLink && (
					<a href={createLink} className="page-title-action">
						{createLinkLabel || __('New Item', 'ctx-events')}
					</a>
				)}
				<hr className="wp-header-end" />
				<StatusSelect
					statusItems={availableStatusItems}
					view={view}
					onViewChange={onChangeView}
				/>
				<Filter fields={fields} view={view} onChangeView={onChangeView} />
				<Table
					items={data}
					titleField={view.titleField}
					mediaField={view.mediaField}
					actions={actions}
					descriptionField={view.descriptionField}
					paginationInfo={paginationInfo}
					fields={view.fields}
					fieldConfig={fields}
					view={view}
					onPageChange={(page) => onChangeView({ ...view, page })}
					onSort={(key) =>
						onChangeView({
							...view,
							sort: {
								field: key,
								direction:
									view.sort.field === key && view.sort.direction === 'asc'
										? 'desc'
										: 'asc',
							},
						})
					}
				/>
			</div>
		</>
	);
};

export default DataTable;
export type { DataTableProps };
