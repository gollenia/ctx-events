import React from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import clsx from 'clsx';

const DataTable = ({ 
    items,          
    columns,        
    view,           
    onPageChange = null,   
	onSort,
    loading,        
    noItemsMessage = __('No items found.', 'ctx-events')
}) => {

    const isFirstPage = view.page === 1;
    const isLastPage = view.page === view.totalPages;

	const getHeaderClasses = (col) => {
        let classes = col.className || '';
        
        // Wenn nicht sortierbar, einfach zurückgeben
        if (!col.sortable) return classes;

        classes += ' sortable'; // Grundklasse für Hover-Effekt

        // Ist diese Spalte gerade aktiv?
        if (view.sortBy === col.key) {
            classes += ' sorted'; // Pfeil anzeigen
            classes += view.sortOrder === 'asc' ? ' asc' : ' desc'; // Pfeilrichtung
        } else {
            classes += ' asc'; // Standard für Inaktive (beim Klick wird es asc)
        }
        return classes;
    };

    return (
        <div className="wp-table-wrapper">
            
            {loading && <p>{__('Loading...', 'ctx-events')}</p>}
            
            {!loading && items.length === 0 && <p>{noItemsMessage}</p>}

            {!loading && items.length > 0 && (
                <>
                    <table className="wp-list-table widefat fixed striped posts">
                        <thead>
                            <tr>
                                <td id="cb" className="manage-column column-cb check-column">
                                    <input id="cb-select-all-1" type="checkbox"/>
                                </td>

                                {columns.map((col, index) => {
									const headerClasses = clsx(
										col.className,
										'manage-column',
										{
											'sortable': col.sortable,
											'sorted': view.sortBy === col.key,
											[view.sortOrder]: view.sortBy === col.key // Dynamischer Key: 'asc' oder 'desc'
										}
									)
                                    return <th 
                                        key={index} 
                                        className={headerClasses}
                                        scope="col"
                                    >
                                        {col.sortable ? (
                                            <a 
                                                href="#" 
                                                onClick={(e) => {
                                                    e.preventDefault();
                                                    onSort(col.key);
                                                }}
                                            >
                                                <span>{col.label}</span>
                                                <span className="sorting-indicator"></span>
                                            </a>
                                        ) : (
                                            col.label
                                        )}
                                    </th>
                                })}
                            </tr>
                        </thead>
                        <tbody>
                            {items.map((item) => (
												console.log(item),
                                <tr key={item.id || Math.random()}>
                                    <th className="cb column-cb check-column">
                                        <input type="checkbox"/>
                                    </th>
                                    
                                    {columns.map((col, index) => (
										console.log(col),
                                        <td key={index} className={col.className || ''}>
                                            {col.render 
                                                ? col.render(item) 
                                                : item[col.id]
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