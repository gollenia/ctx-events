import React from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import clsx from 'clsx';
import Actions from './Actions';
import { useDataTable } from './DataTableContext';
import Pagination from './Pagination';
import type {
	DataFieldConfig,
	DataPaginationInfo,
	DataTableAction,
	DataViewConfig,
} from './types';

interface DataTableProps {
	items: Array<any>;
	fields: Array<string>;
	fieldConfig?: Array<DataFieldConfig>;
	view: DataViewConfig;
	onChangeView: (updates: Partial<DataViewConfig>) => void;
	titleField?: string;
	mediaField?: string;
	paginationInfo?: DataPaginationInfo;
	descriptionField?: string;
	actions?: Array<DataTableAction>;
	loading?: boolean;
	variant?: string;
	noItemsMessage?: string;
}

const Table = ({
	items,
	fieldConfig,
	fields,
	view,
	actions,
	loading,
	variant = 'posts',
	onChangeView,
	noItemsMessage = __('No items found.', 'ctx-events'),
}: DataTableProps) => {
	console.log('Rendering Table with items', items);

	const pluginStatusField = fieldConfig?.find((f) => f.isPluginStatus)?.id;
	console.log('Plugin status field:', pluginStatusField);

	return (
		<div className="wp-table-wrapper">
			{loading && <p>{__('Loading...', 'ctx-events')}</p>}

			{!loading && (
				<table className={`wp-list-table widefat fixed striped ${variant}`}>
					<thead>
						<tr>
							<td
								id="cb"
								style={{ width: '1%' }}
								className="manage-field field-cb check-field"
							>
								<input id="cb-select-all-1" type="checkbox" />
							</td>

							{fields.map((field, index) => {
								const fieldData = fieldConfig?.find((f) => f.id === field);
								if (!fieldData) {
									return (
										<th key={index} scope="col">
											{field}
										</th>
									);
								}
								const headerClasses = clsx(
									fieldData.className,
									'manage-field',
									{
										sortable: fieldData.enableSorting,
										sorted: view.sort.field === fieldData.id,
										[view.sort.direction]: view.sort.field === fieldData.id,
									},
								);
								return (
									<th key={index} className={headerClasses} scope="col">
										{fieldData.enableSorting ? (
											<a
												href="#"
												onClick={(e) => {
													e.preventDefault();
													onChangeView({
														...view,
														sort: {
															field: fieldData.id,
															direction:
																view.sort.field === fieldData.id &&
																view.sort.direction === 'asc'
																	? 'desc'
																	: 'asc',
														},
													});
												}}
											>
												<span>{fieldData.label}</span>
												<span className="sorting-indicator"></span>
											</a>
										) : (
											fieldData.label
										)}
									</th>
								);
							})}
						</tr>
					</thead>
					<tbody>
						{!loading && items.length === 0 && (
							<tr>
								<td colSpan={fields.length}>{noItemsMessage}</td>
							</tr>
						)}
						{items.map((item) => {
							console.log('Rendering row for item', item);
							return (
								<tr
									key={item.id || Math.random()}
									className={
										pluginStatusField && item[pluginStatusField] ? 'active' : ''
									}
								>
									<th className="cb field-cb check-field">
										<input type="checkbox" />
									</th>

									{fields.map((field, index) => {
										const fieldData: DataFieldConfig | false =
											fieldConfig?.find((f) => f.id === field) ?? false;
										if (!fieldData) {
											return <td key={index}>{item[field]}</td>;
										}

										let value: any;

										if (fieldData.render) {
											value = fieldData.render(item);
										} else {
											value = fieldData.getValue
												? fieldData.getValue(item)
												: item[field];
										}

										if (view.titleField === field) {
											value = (
												<>
													<strong>{value}</strong>
													{actions && actions.length > 0 && (
														<Actions
															actions={actions.map((action) => ({
																...action,
																id: item.id,
															}))}
															showOnHover={false}
															item={item}
														/>
													)}
												</>
											);
										}

										return (
											<td key={index} className={fieldData.className}>
												{value}
											</td>
										);
									})}
								</tr>
							);
						})}
					</tbody>
				</table>
			)}
		</div>
	);
};

const DataTableTable = () => {
	const { data, fields, view, actions, onChangeView, isLoading } =
		useDataTable();
	return (
		<Table
			items={data}
			fieldConfig={fields}
			fields={view.fields}
			view={view}
			actions={actions}
			onChangeView={onChangeView}
			loading={isLoading}
		/>
	);
};

export default Table;
export { DataTableTable };
export type { DataTableProps };
