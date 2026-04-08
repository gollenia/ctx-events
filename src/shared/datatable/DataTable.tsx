import { useState } from '@wordpress/element';
import clsx from 'clsx';
import type React from 'react';
import { DataTableContext } from './DataTableContext';
import { DataTableFilter } from './Filter';
import { DataTableHeader } from './Header';
import { DataTablePagination } from './Pagination';
import { DataTableScreenMeta } from './ScreenMeta';
import { DataTableScreenMetaLinks } from './ScreenMetaLinks';
import { DataTableStatusSelect } from './StatusSelect';
import { DataTableTable } from './Table';
import type {
	DataFieldConfig,
	DataPaginationInfo,
	DataStatusItem,
	DataTableAction,
	DataViewConfig,
} from './types';

interface DataTableProps<T> {
	data: Array<T>;
	fields: Array<DataFieldConfig>;
	view: DataViewConfig;
	onChangeView: (updates: Partial<DataViewConfig>) => void;
	isLoading: boolean;
	variant?: 'default' | 'plugins';
	actions?: Array<DataTableAction>;
	paginationInfo?: DataPaginationInfo;
	search?: boolean;
	searchLabel?: string;
	empty?: React.ComponentType;
	availableStatusItems: Array<DataStatusItem>;
	title?: string;
	createLink?: string;
	createLinkLabel?: string;
	children?: React.ReactNode;
	screenMeta?: boolean;
}

type DataTableComponent = (<T extends object>(
	props: DataTableProps<T>,
) => JSX.Element) & {
	Header: React.ComponentType;
	Filter: React.ComponentType;
	Table: React.ComponentType;
	StatusSelect: React.ComponentType;
	Pagination: React.ComponentType;
};

const defaultChildren = () => (
	<>
		<DataTableHeader />
		<DataTableStatusSelect />
		<DataTableFilter />
		<DataTableTable />
		<DataTablePagination />
	</>
);

const DataTable: DataTableComponent = <T extends object>({
	data,
	fields,
	view,
	onChangeView,
	variant,
	actions,
	paginationInfo,
	searchLabel,
	isLoading,
	empty,
	availableStatusItems,
	title,
	createLink,
	createLinkLabel,
	children,
	screenMeta = true,
}: DataTableProps<T>) => {
	const [screenMetaContext, setScreenMetaContext] = useState('');
	return (
		<DataTableContext.Provider
			value={{
				data,
				fields,
				view,
				onChangeView,
				variant,
				actions,
				paginationInfo,
				searchLabel,
				isLoading,
				empty,
				availableStatusItems,
				title,
				createLink,
				createLinkLabel,
				setScreenMetaContext,
				screenMetaContext,
			}}
		>
			{screenMeta && (
				<>
					<DataTableScreenMeta />
					<DataTableScreenMetaLinks />
				</>
			)}

			<div
				className={clsx(
					'wrap ctx-datatable',
					isLoading && 'datatable--loading',
				)}
				style={{ padding: '10px 20px 0 2px', margin: 0 }}
			>
				{children ?? defaultChildren()}
			</div>
		</DataTableContext.Provider>
	);
};

DataTable.Header = DataTableHeader;
DataTable.Filter = DataTableFilter;
DataTable.Table = DataTableTable;
DataTable.StatusSelect = DataTableStatusSelect;
DataTable.Pagination = DataTablePagination;

export default DataTable;
export type { DataTableProps };
