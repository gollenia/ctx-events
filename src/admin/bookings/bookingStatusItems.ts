import { mapStatusItems } from '@events/datatable/statusItems';
import { __ } from '@wordpress/i18n';

const STATUSES = [
	{ value: '1', label: __('Pending', 'ctx-events') },
	{ value: '2', label: __('Approved', 'ctx-events') },
	{ value: '3', label: __('Canceled', 'ctx-events') },
	{ value: '4', label: __('Expired', 'ctx-events') },
];

export const bookingStatusItems = (apiCounts: Record<string, number>) => {
	return mapStatusItems(STATUSES, apiCounts);
};
