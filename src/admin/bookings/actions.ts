import type { DataTableAction } from '@events/datatable/types';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import BookingActionConfirmModal from './BookingActionConfirmModal';

type Booking = { reference: string; status: number };
type ActionResponse = { warnings?: string[] } | null;
type BookingActionOptions = {
	sendMail?: boolean;
	cancellationReason?: string;
};

type BookingActionConfig = {
	id: string;
	label: string;
	path: (reference: string) => string;
	method: 'POST' | 'DELETE';
	data?: Record<string, string>;
	supportsMail: boolean;
	modalHeader: string;
	confirmText: string;
	confirmLabel: string;
	disabled: (item: Booking) => boolean;
	delete?: boolean;
};

type BookingDataTableAction = DataTableAction & BookingActionConfig;

const ACTIONS: BookingActionConfig[] = [
	{
		id: 'approve',
		label: __('Approve', 'ctx-events'),
		path: (reference: string) => `/events/v3/bookings/${reference}/approve`,
		method: 'POST',
		data: { status: 'approved' },
		supportsMail: true,
		RenderModal: BookingActionConfirmModal,
		modalHeader: __('Approve booking', 'ctx-events'),
		confirmText: __(
			'Do you really want to approve this booking?',
			'ctx-events',
		),
		confirmLabel: __('Approve booking', 'ctx-events'),
		disabled: (item) => item.status !== 1,
	},
	{
		id: 'deny',
		label: __('Deny', 'ctx-events'),
		path: (reference: string) => `/events/v3/bookings/${reference}/deny`,
		method: 'POST',
		data: { status: 'rejected' },
		supportsMail: true,
		RenderModal: BookingActionConfirmModal,
		modalHeader: __('Deny booking', 'ctx-events'),
		confirmText: __('Do you really want to deny this booking?', 'ctx-events'),
		confirmLabel: __('Deny booking', 'ctx-events'),
		disabled: (item) => item.status !== 1,
		delete: true,
	},
	{
		id: 'cancel',
		label: __('Cancel', 'ctx-events'),
		path: (reference: string) => `/events/v3/bookings/${reference}/cancel`,
		method: 'POST',
		data: { status: 'canceled' },
		supportsMail: true,
		RenderModal: BookingActionConfirmModal,
		modalHeader: __('Cancel booking', 'ctx-events'),
		confirmText: __(
			'Do you really want to cancel this booking?',
			'ctx-events',
		),
		confirmLabel: __('Cancel booking', 'ctx-events'),
		disabled: (item) => item.status !== 2,
		delete: true,
	},
	{
		id: 'restore',
		label: __('Restore', 'ctx-events'),
		path: (reference: string) => `/events/v3/bookings/${reference}/restore`,
		method: 'POST',
		data: { status: 'restored' },
		supportsMail: false,
		RenderModal: BookingActionConfirmModal,
		modalHeader: __('Restore booking', 'ctx-events'),
		confirmText: __(
			'Do you really want to restore this booking?',
			'ctx-events',
		),
		confirmLabel: __('Restore booking', 'ctx-events'),
		disabled: (item) => item.status !== 3 && item.status !== 4,
	},
	{
		id: 'delete',
		label: __('Delete', 'ctx-events'),
		path: (reference: string) => `/events/v3/bookings/${reference}`,
		method: 'DELETE',
		supportsMail: false,
		RenderModal: BookingActionConfirmModal,
		modalHeader: __('Delete booking', 'ctx-events'),
		confirmText: __(
			'Do you really want to delete this booking?',
			'ctx-events',
		),
		confirmLabel: __('Delete booking', 'ctx-events'),
		disabled: (item) => item.status !== 3 && item.status !== 4,
		delete: true,
	},
];

export const createActions = (
	onWarnings?: (warnings: string[]) => void,
): Array<DataTableAction> => ACTIONS.map((config) => {
	const action: BookingDataTableAction = {
		...config,
		RenderModal: BookingActionConfirmModal,
		callback: async (items, onActionPerformed, options) => {
			await executeBookingAction(
				items[0] as Booking,
				action,
				options as BookingActionOptions | undefined,
				onWarnings,
			);
			onActionPerformed?.(items);
		},
	};

	return action;
});

export const executeBookingAction = async (
	item: Booking,
	action: BookingDataTableAction,
	options?: BookingActionOptions,
	onWarnings?: (warnings: string[]) => void,
): Promise<void> => {
	const response = (await apiFetch({
		path: action.path(item.reference),
		method: action.method,
		data: {
			...(action.data ?? {}),
			...(action.supportsMail ? { sendmail: options?.sendMail ?? true } : {}),
			...(action.id === 'cancel'
				? { cancellation_reason: options?.cancellationReason ?? '' }
				: {}),
		},
	})) as ActionResponse;

	if (response?.warnings?.length) {
		onWarnings?.(response.warnings);
	}
};
