import { formatPrice } from '@events/i18n';
import { Icon } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import { notAllowed, pending, published, swatch } from '@wordpress/icons';
import type { BookingListItem } from 'src/types/types';
import type { DataFieldConfig } from '../../shared/datatable/types';
import { STATUS_LABELS } from './constants';

type FilterOptions = {
	gateways: Array<{ value: string; label: string }>;
};

type FieldCallbacks = {
	onReferenceCopy: (reference: string) => void;
	onReferenceClick: (reference: string) => void;
};

const STATUS_ICONS: Record<number, typeof published> = {
	1: pending,
	2: published,
	3: notAllowed,
	4: swatch,
};

const DAY_IN_MS = 24 * 60 * 60 * 1000;
const HOUR_IN_MS = 60 * 60 * 1000;
const MINUTE_IN_MS = 60 * 1000;
const WARNING_COLOR = '#b32d2e';

const getExpiresCountdown = (
	value: string,
): { label: string; isWarning: boolean } => {
	const expiresAt = new Date(value).getTime();
	const remainingMs = expiresAt - Date.now();

	if (Number.isNaN(expiresAt)) {
		return { label: '—', isWarning: false };
	}

	if (remainingMs <= 0) {
		return {
			label: __('Expired', 'ctx-events'),
			isWarning: true,
		};
	}

	if (remainingMs <= DAY_IN_MS) {
		const hours = Math.floor(remainingMs / HOUR_IN_MS);
		const minutes = Math.floor((remainingMs % HOUR_IN_MS) / MINUTE_IN_MS);

		if (hours <= 0) {
			return {
				label: sprintf(__('%d min', 'ctx-events'), Math.max(1, minutes)),
				isWarning: true,
			};
		}

		return {
			label: sprintf(__('%d h %d min', 'ctx-events'), hours, minutes),
			isWarning: true,
		};
	}

	const days = Math.ceil(remainingMs / DAY_IN_MS);

	return {
		label: sprintf(
			/* translators: %d: remaining days until expiration */
			__('%d days', 'ctx-events'),
			days,
		),
		isWarning: days <= 2,
	};
};

export const createFields = (
	options: FilterOptions,
	callbacks: FieldCallbacks,
): Array<DataFieldConfig> => [
	{
		id: 'reference',
		label: __('Reference', 'ctx-events'),
		enableHiding: false,
		render: (booking: BookingListItem) => (
			<a
				href="#"
				onClick={(event) => {
					event.preventDefault();
					callbacks.onReferenceCopy(booking.reference);
				}}
			>
				{booking.reference}
			</a>
		),
	},
	{
		id: 'name',
		label: __('Name', 'ctx-events'),
		enableHiding: false,
		render: (booking: BookingListItem) => {
			const full = `${booking.name.firstName} ${booking.name.lastName}`.trim();
			return (
				<a
					href="#"
					onClick={(event) => {
						event.preventDefault();
						callbacks.onReferenceClick(booking.reference);
					}}
				>
					{full}
				</a>
			);
		},
	},
	{
		id: 'event',
		label: __('Event', 'ctx-events'),
		render: (booking: BookingListItem) => (
			<a
				href={`/wp-admin/admin.php?page=contexis_events_bookings&event_id=${booking.event.id}`}
			>
				{booking.event.title || '—'}
			</a>
		),
	},
	{
		id: 'date',
		label: __('Date', 'ctx-events'),
		enableSorting: true,
		getValue: (booking: BookingListItem) =>
			new Date(booking.date).toLocaleString(undefined, {
				dateStyle: 'medium',
				timeStyle: 'short',
			}),
	},
	{
		id: 'spaces',
		label: __('Spaces', 'ctx-events'),
		getValue: (booking: BookingListItem) => booking.spaces,
	},
	{
		id: 'status',
		label: __('Status', 'ctx-events'),
		enableSorting: true,
		isVisible: false,
		render: (booking: BookingListItem) => {
			const icon = STATUS_ICONS[booking.status];
			const label = STATUS_LABELS[booking.status] ?? String(booking.status);
			return (
				<span style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
					{icon && <Icon icon={icon} size={16} />}
					{label}
				</span>
			);
		},
		filterBy: {
			id: 'status',
			label: __('Status', 'ctx-events'),
			type: 'text',
			elements: [
				{ value: '1', label: __('Pending', 'ctx-events') },
				{ value: '2', label: __('Approved', 'ctx-events') },
				{ value: '3', label: __('Canceled', 'ctx-events') },
				{ value: '4', label: __('Expired', 'ctx-events') },
			],
		},
	},
	{
		id: 'email',
		label: __('E-Mail', 'ctx-events'),
		render: (booking: BookingListItem) => (
			<a href={`mailto:${booking.email}`}>{booking.email}</a>
		),
	},
	{
		id: 'price',
		label: __('Price', 'ctx-events'),
		getValue: (booking: BookingListItem) =>
			formatPrice(booking.priceSummary.finalPrice),
	},
	{
		id: 'gateway',
		label: __('Gateway', 'ctx-events'),
		render: (booking: BookingListItem) => {
			const countdown = booking.transactionExpiresAt
				? getExpiresCountdown(booking.transactionExpiresAt)
				: null;

			return (
				<div>
					<div>{booking.gateway?.name ?? '—'}</div>
					{booking.transactionId ? (
						<div
							style={{
								fontSize: '12px',
								color: 'var(--wp-admin-theme-color-darker-20)',
							}}
						>
							{__('Transaction', 'ctx-events')}: {booking.transactionId}
						</div>
					) : null}
					{countdown ? (
						<div
							style={{
								fontSize: '12px',
								fontWeight: countdown.isWarning ? 'bold' : 'normal',
								color: countdown.isWarning
									? WARNING_COLOR
									: 'var(--wp-admin-theme-color-darker-20)',
							}}
						>
							{__('Expires', 'ctx-events')}: {countdown.label}
						</div>
					) : null}
				</div>
			);
		},
		getValue: (booking: BookingListItem) =>
			[
				booking.gateway?.name ?? '',
				booking.transactionId ?? '',
				booking.transactionExpiresAt ?? '',
			]
				.filter(Boolean)
				.join(' '),
		filterBy: {
			id: 'gateway',
			label: __('All Gateways', 'ctx-events'),
			type: 'text',
			elements: options.gateways,
		},
	},
];
