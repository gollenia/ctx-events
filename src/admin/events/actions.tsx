import type { DataTableAction } from '@events/datatable';
import { __ } from '@wordpress/i18n';

export const actions: Array<DataTableAction> = [
	{
		id: 'duplicate',
		label: __('Duplicate', 'ctx-events'),
		callback: (items: Array<any>) => {
			const eventId = items[0];
			window.location.href = `/wp-admin/post.php?post=${eventId}&action=edit`;
		},
	},
	{
		id: 'cancel',
		label: __('Cancel', 'ctx-events'),
		delete: true,
		callback: (
			items: Array<any>,
			onActionPerformed?: (items: Array<any>) => void,
		) => {
			console.log('Canceling events with IDs:', items);
			onActionPerformed?.(items);
		},
	},
];
