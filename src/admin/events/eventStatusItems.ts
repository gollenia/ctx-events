import { EventStatus } from '../../types/types';
import { __ } from '@wordpress/i18n';

const STATUS_CONFIG: Record<EventStatus, { label: string, apiKey: string, showEmpty?: boolean }> = {
    publish:   { label: __('Published', 'ctx-events'), apiKey: 'publish', showEmpty: true }, // Check hier: 'publish' vs 'published'!
    draft:     { label: __('Draft', 'ctx-events'), apiKey: 'draft' },
    future:    { label: __('Scheduled', 'ctx-events'), apiKey: 'future' },
    pending:   { label: __('Pending', 'ctx-events'), apiKey: 'pending' },
    private:   { label: __('Private', 'ctx-events'), apiKey: 'private' },
    trash:     { label: __('Trash', 'ctx-events'), apiKey: 'trash' },
    cancelled: { label: __('Cancelled', 'ctx-events'), apiKey: 'cancelled' },
};

export const eventStatusItems = (apiCounts: Record<string, number>) => {
    return Object.entries(STATUS_CONFIG).map(([status, config]) => {
        return {
            value: status,
            label: config.label,
            count: apiCounts[config.apiKey] ?? 0
        };
    });
};