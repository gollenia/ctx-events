import { __ } from '@wordpress/i18n';
import type { EventStatus } from '../../types/types';

const STATUSES = [
	{ value: 'publish', label: __('Published', 'ctx-events') },
	{ value: 'draft', label: __('Draft', 'ctx-events') },
	{ value: 'future', label: __('Scheduled', 'ctx-events') },
	{ value: 'pending', label: __('Pending Review', 'ctx-events') },
	{ value: 'private', label: __('Private', 'ctx-events') },
	{ value: 'cancelled', label: __('Cancelled', 'ctx-events') },
	{ value: 'trash', label: __('Trash', 'ctx-events') },
];

export const eventStatusItems = (apiCounts: Record<string, number>) => {
	return STATUSES.map((status) => {
		return {
			value: status.value as EventStatus,
			label: status.label,
			count: apiCounts[status.value] ?? 0,
		};
	});
};
