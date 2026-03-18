import { mapStatusItems } from '@events/datatable/statusItems';
import { __ } from '@wordpress/i18n';

const STATUSES = [
	{ value: '1', label: __('Pending', 'ctx-events') },
	{ value: '2', label: __('Approved', 'ctx-events') },
	{ value: '3', label: __('Canceled', 'ctx-events') },
	{ value: '4', label: __('Expired', 'ctx-events') },
];

export const bookingStatusItems = (apiCounts: Record<string, number>) => {
	return mapStatusItems(STATUSES, {
		'1': apiCounts['1'] ?? apiCounts.pending ?? 0,
		'2': apiCounts['2'] ?? apiCounts.approved ?? 0,
		'3': apiCounts['3'] ?? apiCounts.canceled ?? 0,
		'4': apiCounts['4'] ?? apiCounts.expired ?? 0,
	});
};
