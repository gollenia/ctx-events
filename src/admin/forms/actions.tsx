import type { DataTableAction } from '@events/datatable';
import apiFetch from '@wordpress/api-fetch';

import { __ } from '@wordpress/i18n';

type FormActionItem = {
	id?: number;
	status?: 'publish' | 'draft' | 'trash';
};

const getFormId = (item: unknown): number | null => {
	if (typeof item === 'number') {
		return item;
	}

	if (item && typeof item === 'object' && 'id' in item) {
		const id = (item as FormActionItem).id;
		return typeof id === 'number' ? id : null;
	}

	return null;
};

const setFormStatus = async (
	formId: number,
	status: 'publish' | 'trash',
): Promise<void> => {
	await apiFetch({
		path: `/events/v3/forms/${formId}/status`,
		method: 'POST',
		data: { status },
	});
};

const duplicateForm = async (formId: number): Promise<void> => {
	await apiFetch({
		path: `/events/v3/forms/${formId}/duplicate`,
		method: 'POST',
	});
};

const actions: Array<DataTableAction> = [
	{
		id: 'duplicate',
		label: __('Duplicate', 'ctx-events'),
		disabled: (item) => {
			return item.status === 'trash';
		},
		callback: async (
			items: Array<unknown>,
			onActionPerformed?: (items: Array<unknown>) => void,
		) => {
			const formId = getFormId(items[0]);
			if (!formId) {
				return;
			}

			await duplicateForm(formId);

			onActionPerformed?.(items);
		},
	},
	{
		id: 'trash',
		label: (item) => {
			return item.status === 'trash'
				? __('Restore', 'ctx-events')
				: __('Delete', 'ctx-events');
		},
		delete: (item) => item.status !== 'trash',
		callback: async (
			items: Array<unknown>,
			onActionPerformed?: (items: Array<unknown>) => void,
		) => {
			const item = items[0] as FormActionItem;
			const formId = getFormId(item);
			if (!formId) {
				return;
			}

			const nextStatus = item?.status === 'trash' ? 'publish' : 'trash';
			const question =
				nextStatus === 'trash'
					? __('Are you sure you want to delete this form?', 'ctx-events')
					: __('Do you want to restore this form?', 'ctx-events');

			if (confirm(question)) {
				await setFormStatus(formId, nextStatus);
				onActionPerformed?.(items);
			}
		},
	},
	{
		id: 'delete',
		label: __('Delete', 'ctx-events'),
		delete: true,
		disabled: (item) => {
			return item.status !== 'trash';
		},
		callback: async (
			items: Array<unknown>,
			onActionPerformed?: (items: Array<unknown>) => void,
		) => {
			const formId = getFormId(items[0]);
			if (!formId) {
				return;
			}

			if (
				confirm(
					__(
						'Are you sure you want to permanently delete this form?',
						'ctx-events',
					),
				)
			) {
				await apiFetch({
					path: `/events/v3/forms/${formId}`,
					method: 'DELETE',
				});
				onActionPerformed?.(items);
			}
		},
	},
];

export default actions;
