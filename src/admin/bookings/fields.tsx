import { formatPrice } from '@events/i18n';
import { Icon } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import {
	drafts,
	notAllowed,
	pending,
	published,
	swatch,
} from '@wordpress/icons';
import type { BookingListItem } from 'src/types/types';
import type {
	DataFieldConfig,
	DataFilterElement,
} from '../../shared/datatable/types';

type FilterOptions = {
	events: Array<DataFilterElement<string>>;
	gateways: Array<DataFilterElement<string>>;
};

type FieldCallbacks = {
	onEventClick: (eventId: string) => void;
	onReferenceClick: (reference: string) => void;
};

const STATUS_ICONS: Record<number, typeof published> = {
	1: pending,
	2: published,
	3: notAllowed,
	4: swatch,
	9: drafts,
};

const STATUS_LABELS: Record<number, string> = {
	1: __('Pending', 'ctx-events'),
	2: __('Approved', 'ctx-events'),
	3: __('Canceled', 'ctx-events'),
	4: __('Expired', 'ctx-events'),
	9: __('Deleted', 'ctx-events'),
};

const currency = (): string =>
	(window as any).eventBlocksLocalization?.currency;

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
					callbacks.onReferenceClick(booking.reference);
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
			return <strong>{full || '—'}</strong>;
		},
	},
	{
		id: 'event',
		label: __('Event', 'ctx-events'),
		render: (booking: BookingListItem) => (
			<a
				href="#"
				onClick={(event) => {
					event.preventDefault();
					callbacks.onEventClick(String(booking.event.id));
				}}
			>
				{booking.event.title || '—'}
			</a>
		),
		filterBy: {
			id: 'event_id',
			label: __('All Events', 'ctx-events'),
			type: 'text',
			elements: options.events,
		},
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
		getValue: (booking: BookingListItem) => booking.email,
	},
	{
		id: 'price',
		label: __('Price', 'ctx-events'),
		getValue: (booking: BookingListItem) => formatPrice(booking.price),
	},
	{
		id: 'gateway',
		label: __('Gateway', 'ctx-events'),
		getValue: (booking: BookingListItem) => booking.gateway?.name ?? '—',
		filterBy: {
			id: 'gateway',
			label: __('All Gateways', 'ctx-events'),
			type: 'text',
			elements: options.gateways,
		},
	},
];
