import React from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import clsx from 'clsx';

type DataTableView = {
	page: number,
	totalPages: number,
	totalItems: number,
	sortBy: string,
	sortOrder: 'asc' | 'desc'
}

type DataTableColumn = {
	id: string|number,
	key: string,
	label: string,
	sortable?: boolean,
	className?: string,
	render?: (item: any) => React.Element
}

type DataTableProps = {
	items: Array<any>,          
	columns: Array<DataTableColumn>,
	view: DataTableView,
	onPageChange?: (page: number) => void,
	onSort?: (key: string) => void,
	loading?: boolean,
	variant?: string,
	noItemsMessage?: string
};	

const DataTable = ({ 
    items,          
    columns,        
    view,           
    onPageChange = undefined,   
	onSort,
    loading,    
	variant = 'posts',    
    noItemsMessage = __('No items found.', 'ctx-events')
}: DataTableProps) => {

    const isFirstPage = view.page === 1;
    const isLastPage = view.page === view.totalPages;

    return (
        <div className="wp-table-wrapper">
            
            {loading && <p>{__('Loading...', 'ctx-events')}</p>}

            {!loading && (
                <>
                    <table className={`wp-list-table widefat fixed striped ${variant}`}>
                        <thead>
                            <tr>
                                <td id="cb" className="manage-column column-cb check-column">
                                    <input id="cb-select-all-1" type="checkbox"/>
                                </td>
								
                                {columns.map((column, index) => {
									const headerClasses = clsx(
										column.className,
										'manage-column',
										{
											'sortable': column.sortable,
											'sorted': view.sortBy === column.key,
											[view.sortOrder]: view.sortBy === column.key 
										}
									)
                                    return <th 
                                        key={index} 
                                        className={headerClasses}
                                        scope="col"
                                    >
                                        {column.sortable ? (
                                            <a 
                                                href="#" 
                                                onClick={(e) => {
                                                    e.preventDefault();
                                                    if (onSort) {
                                                        onSort(column.key);
                                                    }
                                                }}
                                            >
                                                <span>{column.label}</span>
                                                <span className="sorting-indicator"></span>
                                            </a>
                                        ) : (
                                            column.label
                                        )}
                                    </th>
                                })}
                            </tr>
                        </thead>
                        <tbody>
							{!loading && items.length === 0 && 
									<tr><td colSpan={columns.length}>{noItemsMessage}</td></tr>}
                            {items.map((item) => (
												
                                <tr key={item.id || Math.random()} className={item.active ? 'active' : ''}>
                                    <th className="cb column-cb check-column">
                                        <input type="checkbox"/>
                                    </th>
                                    
                                    {columns.map((column, index) => (
						
                                        <td key={index} className={column.className || ''}>
                                            {column.render 
                                                ? column.render(item) 
                                                : item[column.id || column.key]
                                            }
                                        </td>
                                    ))}
                                </tr>
                            ))}
                        </tbody>
                    </table>
					
					{ onPageChange != null &&
                    <div className="tablenav bottom">
                        <div className="tablenav-pages">
                            <span className="displaying-num">{view.totalItems} {__('items', 'ctx-events')}</span>
                            <span className="pagination-links">
                                <button 
                                    className={`tablenav-pages-navspan button ${isFirstPage ? 'disabled' : ''}`}
                                    onClick={() => onPageChange(1)}
                                    disabled={isFirstPage}
                                >«</button>
                                <button 
                                    className={`tablenav-pages-navspan button ${isFirstPage ? 'disabled' : ''}`}
                                    onClick={() => onPageChange(view.page - 1)}
                                    disabled={isFirstPage}
                                >‹</button>
                                
                                <span className="screen-reader-text">{__('Current page', 'ctx-events')}</span>
                                <span id="table-paging" className="paging-input">
                                    <span className="tablenav-paging-text"> {view.page} {__('of', 'ctx-events')} <span className="total-pages">{view.totalPages}</span></span>
                                </span>

                                <button 
                                    className={`next-page button ${isLastPage ? 'disabled' : ''}`} 
                                    onClick={() => onPageChange(view.page + 1)}
                                    disabled={isLastPage}
                                >›</button>
                                <button 
                                    className={`last-page button ${isLastPage ? 'disabled' : ''}`}
                                    onClick={() => onPageChange(view.totalPages)}
                                    disabled={isLastPage}
                                >»</button>
                            </span>
                        </div>
                        <br className="clear"/>
                    </div> }
                </>
            )}
        </div>
    );
};

export default DataTable;
export type { DataTableColumn, DataTableView };