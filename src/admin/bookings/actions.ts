import type { DataTableAction } from '@events/datatable/types';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

type Booking = { reference: string; status: number };

export const createActions = (refresh: () => void): Array<DataTableAction> => [
	{
		id: 'approve',
		label: __('Approve', 'ctx-events'),
		disabled: (item: Booking) => item.status === 2,
		callback: async (items, onActionPerformed) => {
			const item = items[0] as Booking;
			await apiFetch({
				path: `/events/v3/bookings/${item.reference}/approve`,
				method: 'POST',
				data: { status: 'approved' },
			});
			onActionPerformed?.(items);
			refresh();
		},
	},
	{
		id: 'cancel',
		label: __('Cancel', 'ctx-events'),
		disabled: (item: Booking) => item.status === 3 || item.status === 4 || item.status === 9,
		callback: async (items, onActionPerformed) => {
			const item = items[0] as Booking;
			await apiFetch({
				path: `/events/v3/bookings/${item.reference}/cancel`,
				method: 'POST',
				data: { status: 'canceled' },
			});
			onActionPerformed?.(items);
			refresh();
		},
	},
	{
		id: 'delete',
		label: __('Delete', 'ctx-events'),
		disabled: (item: Booking) => item.status !== 9,
		callback: async (items, onActionPerformed) => {
			const item = items[0] as Booking;
			await apiFetch({
				path: `/events/v3/bookings/${item.reference}/delete`,
				method: 'DELETE',
			});
			onActionPerformed?.(items);
			refresh();
		},
	},
	{
		id: 'restore',
		label: __('Restore', 'ctx-events'),
		disabled: (item: Booking) => item.status !== 9,
		callback: async (items, onActionPerformed) => {
			const item = items[0] as Booking;
			await apiFetch({
				path: `/events/v3/bookings/${item.reference}/restore`,
				method: 'POST',
				data: { status: 'restored' },
			});
			onActionPerformed?.(items);
			refresh();
		},
	},
	{
		id: 'cancel',
		label: __('Cancel', 'ctx-events'),
		disabled: (item: Booking) => item.status === 1 || item.status === 3 || item.status === 4 || item.status === 9,
		callback: async (items, onActionPerformed) => {
			const item = items[0] as Booking;
			await apiFetch({
				path: `/events/v3/bookings/${item.reference}/cancel`,
				method: 'POST',
				data: { status: 'canceled' },
			});
			onActionPerformed?.(items);
			refresh();
		},
	}
];
