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

const eventPostId = (event: Event): number | string =>
	event.eventId ?? event.id;

const duplicateEvent = async (
	event: Event,
	dates: Array<string>,
): Promise<void> => {
	await apiFetch({
		path: `/events/v3/events/${eventPostId(event)}/duplicate`,
		method: 'POST',
		data: { dates },
	});
};

const cancelEvent = async (
	event: Event,
	options?: EventCancelOptions,
): Promise<void> => {
	await apiFetch({
		path: `/events/v3/events/${eventPostId(event)}/cancel`,
		method: 'POST',
		data: {
			notifyAttendees: options?.notifyAttendees ?? true,
			attendee_message: options?.cancellationReason ?? '',
			cancellation_reason: options?.cancellationReason ?? '',
		},
	});
};

export const createActions = (
	onDetached: (occurrenceId: string, eventId: number) => void,
	onCancelled: (occurrenceId: string) => void,
): Array<DataTableAction> => [
	{
		id: 'edit_recurrence',
		label: __('Bearbeiten', 'ctx-events'),
		disabled: (event) =>
			event.type !== 'virtual' ||
			event.seriesId === null ||
			event.seriesId === undefined,
		callback: (items) => {
			const occurrence = items[0];
			if (occurrence?.seriesId) {
				window.location.href = `/wp-admin/post.php?post=${occurrence.seriesId}&action=edit`;
			}
		},
	},
	{
		id: 'detach_recurrence',
		label: __('Ausgliedern', 'ctx-events'),
		disabled: (event) =>
			event.type !== 'virtual' ||
			event.seriesId === null ||
			event.seriesId === undefined,
		callback: async (items, onActionPerformed) => {
			const occurrence = items[0];
			if (!occurrence || occurrence.type !== 'virtual' || !occurrence.seriesId)
				return;
			const response = await apiFetch<{ eventId: number }>({
				path: `/events/v3/events/${occurrence.seriesId}/detach-occurrence`,
				method: 'POST',
				data: { occurrence_key: occurrence.id },
			});
			onDetached(occurrence.id, response.eventId);
			onActionPerformed?.([occurrence]);
		},
	},
	{
		id: 'cancel_recurrence',
		label: __('Cancel instance', 'ctx-events'),
		delete: true,
		disabled: (event) =>
			event.type !== 'virtual' ||
			event.seriesId === null ||
			event.seriesId === undefined,
		callback: async (items, onActionPerformed) => {
			const occurrence = items[0];
			if (!occurrence || occurrence.type !== 'virtual' || !occurrence.seriesId)
				return;

			await apiFetch({
				path: `/events/v3/events/${occurrence.seriesId}/cancel-occurrence`,
				method: 'POST',
				data: { occurrence_key: occurrence.id },
			});
			onCancelled(occurrence.id);
			onActionPerformed?.([occurrence]);
		},
	},
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
		disabled: (event) =>
			event.type === 'virtual' || !event.bookingSummary?.isBookable,
	},
	{
		id: 'duplicate',
		label: __('Duplicate', 'ctx-events'),
		disabled: (event) => event.type === 'virtual' || event.status === 'trash',
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
		label: __('Cancel event', 'ctx-events'),
		delete: true,
		RenderModal: EventCancelConfirmModal,
		modalHeader: __('Cancel event', 'ctx-events'),
		disabled: (event) => event.type === 'virtual',
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

// Calendar consumers do not keep the table's local rows, but still require
// the same action definitions.
export const actions = createActions(
	() => undefined,
	() => undefined,
);
