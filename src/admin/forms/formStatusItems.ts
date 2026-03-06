import { mapStatusItems } from '@events/datatable/statusItems';
import { __ } from '@wordpress/i18n';

const STATUSES = [
	{ value: 'publish', label: __('Published', 'ctx-events'), showEmpty: true },
	{ value: 'draft', label: __('Draft', 'ctx-events'), showEmpty: true },
	{ value: 'trash', label: __('Trash', 'ctx-events'), showEmpty: true },
];

export const formStatusItems = (apiCounts: Record<string, number>) => {
	return mapStatusItems(STATUSES, apiCounts);
};
