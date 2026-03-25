import { formatDateRange, formatPrice, formatPriceRange } from '@events/i18n';
import { Icon } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { error, notAllowed, pending, published } from '@wordpress/icons';
import type {
	DataFieldConfig,
	DataFilterElement,
} from '../../shared/datatable/types';
import type { Event, TimeScope } from '../../types/types';
import { BookingGraph } from './BookingGraph';
import { bookingDenyReason } from './bookingDenyReason';

const scopeElements: DataFilterElement<TimeScope>[] = [
	{ value: 'future', label: __('Future Events', 'ctx-events') },
	{ value: 'past', label: __('Past Events', 'ctx-events') },
	{ value: 'today', label: __('Today', 'ctx-events') },
	{ value: 'this-week', label: __('This Week', 'ctx-events') },
	{ value: 'this-month', label: __('This Month', 'ctx-events') },
	{ value: 'this-year', label: __('This Year', 'ctx-events') },
];

const bookingsAdminUrl = (eventId: number): string =>
	`/wp-admin/admin.php?page=contexis_events_bookings&event_id=${eventId}`;

export const fields: Array<DataFieldConfig> = [
	{
		label: __('Name', 'ctx-events'),
		id: 'title',
		enableHiding: false,
		className: 'column-title column-primary has-row-actions',
		enableSorting: true,
		render: (event: Event) => (
			<>
				<strong>
					<a href={`/wp-admin/post.php?post=${event.id}&action=edit`}>
						{event.name || __('(No title)', 'ctx-events')}
					</a>
				</strong>
			</>
		),
	},
	{
		label: __('Date', 'ctx-events'),
		id: 'date',
		enableSorting: true,
		getValue: (event: Event) =>
			formatDateRange(event.startDate, event.endDate ?? false),
		filterBy: {
			id: 'scope',
			label: __('Date', 'ctx-events'),
			type: 'date',
			elements: scopeElements,
		},
	},

	{
		label: __('Location', 'ctx-events'),
		id: 'location',
		render: (event: Event) => {
			if (event.includes?.location) {
				return (
					<>
						<strong>
							<a
								href={`/wp-admin/post.php?post=${event.includes.location.id}&action=edit`}
							>
								{event.includes.location.name ||
									__('(No Location)', 'ctx-events')}
							</a>
						</strong>
						<br />
						{event.includes.location.address.addressLocality},{' '}
						{event.includes.location.address.addressRegion}
					</>
				);
			}
			return <>—</>;
		},
		enableSorting: true,
	},
	{
		label: __('Tags', 'ctx-events'),
		id: 'tags',
		getValue: (event: Event) =>
			event.includes?.tags?.map((t: { name: string }) => t.name).join(', '),
		enableSorting: true,
	},
	{
		id: 'categories',
		label: __('Categories', 'ctx-events'),
		getValue: (event: Event) =>
			event.includes?.categories
				?.map((c: { name: string }) => c.name)
				.join(', '),
		enableSorting: true,
	},
	{
		id: 'price',
		label: __('Price', 'ctx-events'),
		getValue: (event: Event) => {
			if (!event.bookingSummary) {
				return '—';
			}
			console.log(
				'Rendering price for event:',
				event.name,
				event.bookingSummary,
			);
			if (
				event.bookingSummary.lowestPrice &&
				event.bookingSummary.highestPrice
			) {
				return `${formatPriceRange(
					event.bookingSummary.lowestPrice,
					event.bookingSummary.highestPrice,
				)}`;
			}

			return __('N/A', 'ctx-events');
		},
		enableSorting: true,
	},
	{
		id: 'bookable',
		label: __('Bookings', 'ctx-events'),
		render: (event: Event) => {
			if (!event.bookingSummary) {
				return (
					<span style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
						<Icon icon={notAllowed} size={16} />
						{__('N/A', 'ctx-events')}
					</span>
				);
			}
			if (!event.bookingSummary.isBookable) {
				return (
					<span style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
						<Icon icon={error} size={16} />
						{bookingDenyReason(event.bookingSummary)}
					</span>
				);
			}
			return (
				<span style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
					<Icon icon={published} size={16} />
					<a className="trashed" href={bookingsAdminUrl(event.id)}>
						{__('View Bookings', 'ctx-events')}
					</a>
				</span>
			);
		},
		enableSorting: true,
	},
	{
		id: 'availability',
		label: __('Availability', 'ctx-events'),
		render: (event: Event) => {
			return event.bookingSummary ? (
				<BookingGraph bookingSummary={event.bookingSummary} />
			) : (
				<>—</>
			);
		},
		enableSorting: true,
	},
	{
		id: 'bookings',
		label: __('Bookings', 'ctx-events'),
		render: (event: Event) => (
			<a href={bookingsAdminUrl(event.id)}>
				{__('View Bookings', 'ctx-events')}
			</a>
		),
	},
	{
		id: 'status',
		label: __('Status', 'ctx-events'),
		getValue: (event: Event) => event.status,
		enableSorting: true,
		isVisible: false,
		filterBy: {
			id: 'status',
			label: __('Status', 'ctx-events'),
			type: 'text',
			elements: [
				{ value: 'publish', label: __('Published', 'ctx-events') },
				{ value: 'draft', label: __('Draft', 'ctx-events') },
				{ value: 'trash', label: __('Trashed', 'ctx-events') },
			],
		},
	},
];
