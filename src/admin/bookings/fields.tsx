import { formatPrice } from '@events/i18n';
import { Icon } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { drafts, notAllowed, pending, published, swatch } from '@wordpress/icons';
import type { DataFieldConfig, DataFilterElement } from '../../shared/datatable/types';

type Booking = {
	reference: string;
	email: string;
	name: { first: string; last: string };
	event: { id: number; title: string };
	status: number;
	price: number;
	donation: number;
	spaces: number;
	gateway: { slug: string; name: string } | null;
	tickets: Array<{ ticketId: string; count: number }>;
	date: string;
};

type FilterOptions = {
	events: Array<DataFilterElement<string>>;
	gateways: Array<DataFilterElement<string>>;
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
	(window as any).eventBlocksLocalization?.currency ?? 'EUR';

export const createFields = (options: FilterOptions): Array<DataFieldConfig> => [
	{
		id: 'name',
		label: __('Name', 'ctx-events'),
		enableHiding: false,
		render: (booking: Booking) => {
			const full = `${booking.name.first} ${booking.name.last}`.trim();
			return <strong>{full || '—'}</strong>;
		},
	},
	{
		id: 'event',
		label: __('Event', 'ctx-events'),
		render: (booking: Booking) => (
			<a href={`/wp-admin/post.php?post=${booking.event.id}&action=edit`}>
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
		getValue: (booking: Booking) =>
			new Date(booking.date).toLocaleString(undefined, {
				dateStyle: 'medium',
				timeStyle: 'short',
			}),
	},
	{
		id: 'spaces',
		label: __('Spaces', 'ctx-events'),
		getValue: (booking: Booking) => booking.spaces,
	},
	{
		id: 'status',
		label: __('Status', 'ctx-events'),
		enableSorting: true,
		isVisible: false,
		render: (booking: Booking) => {
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
		getValue: (booking: Booking) => booking.email,
	},
	{
		id: 'price',
		label: __('Price', 'ctx-events'),
		getValue: (booking: Booking) => formatPrice(booking.price / 100, currency()),
	},
	{
		id: 'gateway',
		label: __('Gateway', 'ctx-events'),
		getValue: (booking: Booking) => booking.gateway?.name ?? '—',
		filterBy: {
			id: 'gateway',
			label: __('All Gateways', 'ctx-events'),
			type: 'text',
			elements: options.gateways,
		},
	},
];
