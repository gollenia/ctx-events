import React, { useState, useMemo } from '@wordpress/element';
import DataTable, { DataTableField, DataTableView } from './DataTable';
import DataFilter, { DataFilterField } from './DataFilter';
import StatusSelect, { StatusItem } from './StatusSelect';
import { RowAction } from './RowActions';

type DataPaginationInfo = {
	totalPages: number,
	totalItems: number
};

type PostTableProps = {
	data: Array<any>, 
	type?: 'table' | 'grid' | 'list',
	search: string,
	filters: Array<DataFilterField>,	
	fields: Array<DataTableField>,
	view: DataTableView,
	availableStatusItems: Array<StatusItem>,
	sort: (key: string) => void,
	loading?: boolean,
	actions?: Array<RowAction>,
	onChangeView: (view: DataTableView) => void,
	paginationInfo?: DataPaginationInfo,
	
	noItemsMessage?: string
};	


type DataTableView = {
	page: number,
	perPage: number,
	totalPages: number,
	totalItems: number,
	filters?: Array<DataFilterField>,
	search?: string,
	sortBy: string,
	titleField?: string,
	sortOrder: 'asc' | 'desc'
}

const PostTable = ({ 
	items, 
	fields, 
	filters,
	view,
	actions,
	availableStatusItems, 
	onChangeView }: PostTableProps) => {
    
    const [view, setView] = useState<DataTableView>({
        page: 1,
        perPage: 20,
        sortBy: 'date',
        sortOrder: 'desc',
        totalPages: Math.ceil(totalItems / 20),
        totalItems: totalItems,
        // Hier können dynamische Filter-Keys landen
    });

    // Handler für Filter-Änderungen (Selects, Text-Suche)
    const handleFilterChange = (key: string, value: any) => {
        setView(prev => ({
            ...prev,
            [key]: value,
            page: 1 // Zurück auf Seite 1 bei Filteränderung
        }));
    };

    // Handler für Status-Links (subsubsub)
    const handleStatusChange = (status: string) => {
        handleFilterChange('status', status);
    };

    return (
        <div className="wrap">
            <h1 className="wp-heading-inline">{__('Mein Plugin Content', 'ctx-events')}</h1>
            <hr className="wp-header-end" />

            {/* 1. Status Links (All | Published | Trash) */}
            <StatusSelect 
                statusItems={statusItems} 
                currentStatus={view['status'] || ''} 
                onChange={handleStatusChange} 
            />

            {/* 2. Filter Bar (Search, Category-Select etc.) */}
            <DataFilter 
                filters={filters} 
                view={view} 
                onFilterChange={handleFilterChange} 
            />

            {/* 3. Die eigentliche Tabelle */}
            <DataTable 
                items={items}
                columns={columns}
                view={view}
                onPageChange={(page) => setView(prev => ({ ...prev, page }))}
                onSort={(key) => setView(prev => ({
                    ...prev,
                    sortBy: key,
                    sortOrder: prev.sortBy === key && prev.sortOrder === 'asc' ? 'desc' : 'asc'
                }))}
            />
        </div>
    );
};

export default PostTable;