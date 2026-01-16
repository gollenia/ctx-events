import React from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const DataFilter = ({ filters, view, onFilterChange }) => {
    
    const renderInput = (filter) => {
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