import type { DataTableAction } from '@events/datatable';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import type { RecurringEventListItem } from './types';

export const recurringEventActions: DataTableAction[] = [
	{
		id: 'edit',
		label: __('Edit', 'ctx-events'),
		callback: (items) => {
			const event = items[0] as RecurringEventListItem | undefined;
			if (!event) return;

			window.location.href = `/wp-admin/post.php?post=${event.id}&action=edit`;
		},
	},
	{
		id: 'cancel',
		label: __('Cancel event', 'ctx-events'),
		delete: true,
		disabled: (item: RecurringEventListItem) =>
			item.status === 'cancelled' || item.status === 'trash',
		callback: async (items, onActionPerformed) => {
			const event = items[0] as RecurringEventListItem | undefined;
			if (!event) return;

			await apiFetch({
				path: `/wp/v2/ctx-event-recurring/${event.id}`,
				method: 'POST',
				data: { status: 'cancelled' },
			});
			onActionPerformed?.([event]);
		},
	},
];
