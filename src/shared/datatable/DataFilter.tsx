import React from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { DataTableView } from './DataTable';

type DataFilterField = {
	key: string,
	label: string,
	type: 'select' | 'text' | 'search' | 'checkbox',
	elements?: Array<DataFilterElement>,
	filterBy?: string,
};

type DataFilterElement = {
	value: string,
	label: string
}

type DataFilterProps = {
	filters: Array<DataFilterField>,
	view: DataTableView,
	onFilterChange: (key: string, value: any) => void
};

const DataFilter = ({ filters, view, onFilterChange }: DataFilterProps) => {
    
    const renderInput = (filter: DataFilterField) => {
        switch (filter.type) {
            case 'select':
                return (
                    <select 
                        key={filter.key}
                        name={filter.key}
                        value={view[filter.key] || ''} 
                        onChange={(e) => onFilterChange(filter.key, e.target.value)}
                        className="postform" 
                        style={{ marginRight: '5px' }} 
                    >
                        {filter.label && <option value="">{filter.label}</option>}
                        
                        {Object.entries(filter.options).map(([value, label]) => (
                            <option key={value} value={value}>
                                {label}
                            </option>
                        ))}
                    </select>
                );

            case 'text':
            case 'search':
                return (
                   <input
                        key={filter.key}
                        type="search" 
                        placeholder={filter.label}
                        value={view[filter.key] || ''}
                        onChange={(e) => onFilterChange(filter.key, e.target.value)}
                        className="form-control"
                        style={{ marginRight: '5px', height: '30px' }} 
                   />
                );

			case 'checkbox':
				return (
					<label key={filter.key} style={{ marginRight: '10px' }}>
						<input
							type="checkbox"
							checked={!!view[filter.key]}
							onChange={(e) => onFilterChange(filter.key, e.target.checked)}
							style={{ marginRight: '5px' }}
						/>
						{filter.label}
					</label>
				);

            default:
                return null;
        }
    };

    return (
        <div className="tablenav top">
            <div className="alignleft actions bulkactions">
                {filters.map(filter => renderInput(filter))}
            </div>
            <br className="clear" />
        </div>
    );
};

export default DataFilter;
export type { DataFilterField, DataFilterProps };