import { __ } from '@wordpress/i18n';
import React from '@wordpress/element';

type StatusItem = {
    label: string;
    value: string;
	count: number;
	showEmpty?: boolean; 
}

type StatusSelectProps = {
    statusItems?: Array<StatusItem>;
    currentStatus: string; // Neu: Wir müssen wissen, was aktiv ist
    onChange: (status: string) => void;
}

const StatusSelect = ({ statusItems = [], currentStatus, onChange }: StatusSelectProps) => {
    
    if (statusItems.length === 0) return null;

    return (
        <ul className="subsubsub">
            {statusItems.filter((item: StatusItem) => item.showEmpty || item.count > 0).map((item, index, visibleItems) => {
                return (
					<li key={item.value}>
						<a 
							href={`#${item.value}`}
							className={currentStatus === item.value ? 'current' : ''} 
                        onClick={(e) => {
                            e.preventDefault();
                            onChange(item.value);
                        }}
                    >
                        {item.label}
                    </a> ({item.count})
                    {index < visibleItems.length - 1 && ' | '}
                </li>
            	)})}
        </ul>
    );
}

export default StatusSelect;
export type { StatusSelectProps, StatusItem };