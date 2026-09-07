import type { DataTableAction } from '@events/datatable';
import apiFetch from '@wordpress/api-fetch';
import { __, sprintf } from '@wordpress/i18n';
import type { Event } from '../../types/types';
import EventCancelConfirmModal from './EventCancelConfirmModal';
import EventDuplicateModal from './EventDuplicateModal';

type EventCancelOptions = {
	notifyAttendees?: boolean;
	cancellationReason?: string;
};

type EventDuplicateOptions = {
	dates?: Array<string>;
};

type EventActionConfig = {
	id: string;
	label: string;
	delete?: boolean;
	disabled?: (event: Event) => boolean;
	RenderModal?: DataTableAction['RenderModal'];
	modalHeader?: string;
	confirmText?: (item: Event) => string;
	confirmLabel?: string;
	callback: (
		items: Array<Event>,
		onActionPerformed?: (items: Array<any>) => void,
		options?: EventCancelOptions | EventDuplicateOptions,
	) => void | Promise<void>;
};

const duplicateEvent = async (
	event: Event,
	dates: Array<string>,
): Promise<void> => {
	await apiFetch({
		path: `/events/v3/events/${event.id}/duplicate`,
		method: 'POST',
		data: { dates },
	});
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
			attendee_message: options?.cancellationReason ?? '',
			cancellation_reason: options?.cancellationReason ?? '',
		},
	});
};

const ACTIONS: Array<EventActionConfig> = [
	{
		id: 'view_bookings',
		label: __('Bookings', 'ctx-events'),
		callback: (items) => {
			const event = items[0];
			if (!event) {
				return;
			}

			window.location.href = `/wp-admin/admin.php?page=contexis_events_bookings&event_id=${event.id}`;
		},
		disabled: (event) => !event.bookingSummary?.isBookable,
	},
	{
		id: 'duplicate',
		label: __('Duplicate', 'ctx-events'),
		disabled: (event) => event.status === 'trash',
		RenderModal: EventDuplicateModal,
		modalHeader: __('Duplicate event', 'ctx-events'),
		callback: async (items, onActionPerformed, options) => {
			const event = items[0];
			if (!event) {
				return;
			}

			const dates = (options as EventDuplicateOptions | undefined)?.dates ?? [];
			await duplicateEvent(event, dates);
			onActionPerformed?.([event]);
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
			const event = items[0];
			if (!event) {
				return;
			}

			await cancelEvent(event, options as EventCancelOptions | undefined);
			onActionPerformed?.([event]);
		},
	},
];

export const actions: Array<DataTableAction> =
	ACTIONS as Array<DataTableAction>;
