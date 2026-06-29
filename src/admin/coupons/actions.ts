import type { DataTableAction } from '@events/datatable';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import BulkDuplicateCouponModal from './BulkDuplicateCouponModal';

type CouponActionItem = {
	id?: number;
	status?: 'publish' | 'draft' | 'trash' | 'future' | 'private';
};

const getCouponId = (item: unknown): number | null => {
	if (typeof item === 'number') {
		return item;
	}

	if (item && typeof item === 'object' && 'id' in item) {
		const id = (item as CouponActionItem).id;
		return typeof id === 'number' ? id : null;
	}

	return null;
};

const setCouponStatus = async (
	couponId: number,
	status: 'publish' | 'trash',
): Promise<void> => {
	await apiFetch({
		path: `/events/v3/coupons/${couponId}/status`,
		method: 'POST',
		data: { status },
	});
};

const actions: Array<DataTableAction> = [
	{
		id: 'bulk-duplicate',
		label: __('Bulk Multiply', 'ctx-events'),
		RenderModal: BulkDuplicateCouponModal,
		disabled: (item) => item.status === 'trash',
		callback: async (
			items: Array<unknown>,
			onActionPerformed?: (items: Array<unknown>) => void,
			options?: Record<string, unknown>,
		) => {
			const couponId = getCouponId(items[0]);
			if (!couponId) {
				return;
			}

			await apiFetch({
				path: `/events/v3/coupons/${couponId}/bulk-duplicate`,
				method: 'POST',
				data: {
					count: Number(options?.count ?? 1),
				},
			});

			onActionPerformed?.(items);
		},
	},
	{
		id: 'trash',
		label: (item) =>
			item.status === 'trash' ? __('Restore', 'ctx-events') : __('Delete', 'ctx-events'),
		delete: (item) => item.status !== 'trash',
		callback: async (
			items: Array<unknown>,
			onActionPerformed?: (items: Array<unknown>) => void,
		) => {
			const item = items[0] as CouponActionItem;
			const couponId = getCouponId(item);
			if (!couponId) {
				return;
			}

			const nextStatus = item.status === 'trash' ? 'publish' : 'trash';
			const question =
				nextStatus === 'trash'
					? __('Are you sure you want to delete this coupon?', 'ctx-events')
					: __('Do you want to restore this coupon?', 'ctx-events');

			if (confirm(question)) {
				await setCouponStatus(couponId, nextStatus);
				onActionPerformed?.(items);
			}
		},
	},
	{
		id: 'delete',
		label: __('Delete permanently', 'ctx-events'),
		delete: true,
		disabled: (item) => item.status !== 'trash',
		callback: async (
			items: Array<unknown>,
			onActionPerformed?: (items: Array<unknown>) => void,
		) => {
			const couponId = getCouponId(items[0]);
			if (!couponId) {
				return;
			}

			if (
				confirm(
					__(
						'Are you sure you want to permanently delete this coupon?',
						'ctx-events',
					),
				)
			) {
				await apiFetch({
					path: `/events/v3/coupons/${couponId}`,
					method: 'DELETE',
				});
				onActionPerformed?.(items);
			}
		},
	},
];

export default actions;
