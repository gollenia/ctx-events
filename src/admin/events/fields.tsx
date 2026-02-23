import { formatDateRange } from '@events/i18n';
import { __ } from '@wordpress/i18n';
import type {
	DataFieldConfig,
	DataFilterElement,
} from '../../shared/datatable/types';
import type { Event, TimeScope } from '../../types/types';
import { bookingDenyReason } from './bookingDenyReason';

const scopeElements: DataFilterElement<TimeScope>[] = [
	{ value: 'future', label: __('Future Events', 'ctx-events') },
	{ value: 'past', label: __('Past Events', 'ctx-events') },
	{ value: 'today', label: __('Today', 'ctx-events') },
	{ value: 'this-week', label: __('This Week', 'ctx-events') },
	{ value: 'this-month', label: __('This Month', 'ctx-events') },
	{ value: 'this-year', label: __('This Year', 'ctx-events') },
];

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
				if (
					event.bookingSummary.lowestPrice.amountCents ===
					event.bookingSummary.highestPrice.amountCents
				) {
					return `${(event.bookingSummary.lowestPrice.amountCents / 100).toFixed(2)} ${event.bookingSummary.lowestPrice.currency}`;
				}
				return `${(event.bookingSummary.lowestPrice.amountCents / 100).toFixed(2)} - ${(event.bookingSummary.highestPrice.amountCents / 100).toFixed(2)} ${event.bookingSummary.lowestPrice.currency}`;
			}

			return __('N/A', 'ctx-events');
		},
		enableSorting: true,
	},
	{
		id: 'bookable',
		label: __('Bookable', 'ctx-events'),
		render: (event: Event) => {
			if (!event.bookingSummary) {
				return <>—</>;
			}
			if (!event.bookingSummary.isBookable) {
				return (
					<span className="danger">
						{bookingDenyReason(event.bookingSummary)}
					</span>
				);
			}
			return (
				<span className="trashed">
					{event.bookingSummary.isBookable
						? __('Yes', 'ctx-events')
						: __('No', 'ctx-events')}
				</span>
			);
		},
		enableSorting: true,
	},
];
