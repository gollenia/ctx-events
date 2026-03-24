import type { DataTableAction } from '@events/datatable';
import apiFetch from '@wordpress/api-fetch';
import { __, sprintf } from '@wordpress/i18n';
import type { Event } from '../../types/types';
import EventCancelConfirmModal from './EventCancelConfirmModal';

type EventCancelOptions = {
	notifyAttendees?: boolean;
};

type EventActionConfig = {
	id: string;
	label: string;
	delete?: boolean;
	RenderModal?: DataTableAction['RenderModal'];
	modalHeader?: string;
	confirmText?: (item: Event) => string;
	confirmLabel?: string;
	callback: (
		items: Array<Event>,
		onActionPerformed?: (items: Array<any>) => void,
		options?: EventCancelOptions,
	) => void | Promise<void>;
};

type EventCancelAction = DataTableAction & {
	confirmText: (item: Event) => string;
	confirmLabel: string;
};

const duplicateEvent = (event: Event) => {
	window.location.href = `/wp-admin/post.php?post=${event.id}&action=edit`;
};

const cancelEvent = async (
	event: Event,
	options?: EventCancelOptions,
): Promise<void> => {
	await apiFetch({
		path: `/events/v3/events/${event.id}/cancel`,
		method: 'POST',
		data: {
			notifyAttendees: options?.notifyAttendees ?? true,
		},
	});
};

const ACTIONS: Array<EventActionConfig> = [
	{
		id: 'duplicate',
		label: __('Duplicate', 'ctx-events'),
		callback: (items) => {
			duplicateEvent(items[0]);
		},
	},
	{
		id: 'cancel',
		label: __('Cancel', 'ctx-events'),
		delete: true,
		RenderModal: EventCancelConfirmModal,
		modalHeader: __('Cancel event', 'ctx-events'),
		confirmText: (event) =>
			sprintf(
				/* translators: %s: event title */
				__('Do you really want to cancel "%s"?', 'ctx-events'),
				event.name || __('(No title)', 'ctx-events'),
			),
		confirmLabel: __('Cancel event', 'ctx-events'),
		callback: async (items, onActionPerformed, options) => {
			await cancelEvent(items[0], options);
			onActionPerformed?.(items);
		},
	},
];

export const actions: Array<DataTableAction> = ACTIONS as Array<
	DataTableAction
>;
