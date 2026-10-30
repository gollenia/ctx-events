import { formatDate, formatDateRange } from '@events/i18n';
import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type { EventsTableAttributes, EventsTableEvent } from './types';

const truncate = (text: string, maxWords: number): string => {
	if (text.length === 0 || maxWords === 0) return '';

	const words = text.split(' ');
	return words.length <= maxWords
		? text
		: `${words.slice(0, maxWords).join(' ')}...`;
};

const getLocationLabel = (
	event: EventsTableEvent,
	showLocation: EventsTableAttributes['showLocation'],
): string =>
	showLocation === 'name' || showLocation === 'city'
		? event.location[showLocation]
		: '';

const renderBookingWarning = (
	event: EventsTableEvent,
	showBookedUp: boolean,
	warningThreshold: number,
) => {
	if (!showBookedUp || !event.bookings?.hasBookings) return null;
	if (
		event.bookings.spaces === null &&
		event.bookings.denyReason !== 'sold_out'
	)
		return null;
	if (
		event.bookings.spaces !== null &&
		event.bookings.spaces > warningThreshold
	)
		return null;

	return event.bookings.spaces !== null && event.bookings.spaces > 0 ? (
		<span className="ctx-events-table__pill">
			{__('Nearly Booked up', 'ctx-events')}
		</span>
	) : (
		<span className="ctx-events-table__pill ctx-events-table__pill--error">
			{__('Booked up', 'ctx-events')}
		</span>
	);
};

type Props = {
	attributes: EventsTableAttributes;
	events: EventsTableEvent[];
};

export default function Table({ attributes, events }: Props) {
	const {
		showCategory,
		showLocation,
		showBookedUp,
		bookedUpWarningThreshold,
		excerptLength,
		showAudience,
		showPerson,
		tableShowHeader,
		tableShowDividers,
		tableAlternatingRows,
		tableHoverEffect,
		tableShowCalendarIcon,
		tableColumns,
	} = attributes;
	const configuredColumns = tableColumns ?? [
		'title',
		...(showCategory ? ['category'] : []),
		'description',
		...(showAudience ? ['audience'] : []),
		...(showPerson ? ['person'] : []),
		...(showLocation ? ['location'] : []),
		...(showBookedUp ? ['booking'] : []),
	];
	const columns =
		tableShowCalendarIcon && !configuredColumns.includes('date')
			? ['date', ...configuredColumns]
			: configuredColumns;
	const className = [
		'ctx-events-table',
		tableShowDividers ? '' : 'ctx-events-table--no-dividers',
		tableAlternatingRows ? 'ctx-events-table--alternating' : '',
		tableHoverEffect ? 'ctx-events-table--hover' : '',
	]
		.filter(Boolean)
		.join(' ');
	const labels: Record<string, string> = {
		date: __('Date', 'ctx-events'),
		title: __('Event', 'ctx-events'),
		category: __('Category', 'ctx-events'),
		description: __('Description', 'ctx-events'),
		audience: __('Audience', 'ctx-events'),
		person: __('Person', 'ctx-events'),
		location: __('Location', 'ctx-events'),
		booking: __('Booking', 'ctx-events'),
	};
	const renderCell = (event: EventsTableEvent, column: string) => {
		switch (column) {
			case 'date':
				return (
					<td className="ctx-events-table__date">
						{tableShowCalendarIcon && (
							<div className="ctx-events-table-date">
								<span className="ctx-events-table-date__day">
									{formatDate(event.start, { day: 'numeric' })}
								</span>
								<span className="ctx-events-table-date__month">
									{formatDate(event.start, { month: 'short' })}
								</span>
							</div>
						)}
					</td>
				);
			case 'title':
				return (
					<td className="ctx-events-table__title">
						<a href={event.link}>
							<b>{event.title}</b>
						</a>
						<div>{formatDateRange(event.start, event.end)}</div>
					</td>
				);
			case 'category':
				return (
					<td className="ctx-events-table__label">{event.category?.name}</td>
				);
			case 'description':
				return (
					<td className="ctx-events-table__text">
						{truncate(event.excerpt, excerptLength)}
					</td>
				);
			case 'audience':
				return <td className="ctx-events-table__text">{event.audience}</td>;
			case 'person':
				return <td className="ctx-events-table__text">{event.person?.name}</td>;
			case 'location':
				return (
					<td className="ctx-events-table__text">
						{getLocationLabel(event, showLocation)}
					</td>
				);
			case 'booking':
				return (
					<td>
						{renderBookingWarning(
							event,
							showBookedUp,
							bookedUpWarningThreshold,
						)}
					</td>
				);
			default:
				return null;
		}
	};

	return (
		<table className={className} cellPadding={0} cellSpacing={0}>
			{tableShowHeader && (
				<thead>
					<tr>
						{columns.map((column) => (
							<th key={column} scope="col">
								{labels[column]}
							</th>
						))}
					</tr>
				</thead>
			)}
			<tbody>
				{events.map((event) => (
					<tr
						className="ctx-events-table__row"
						key={event.id}
						onClick={() => {
							window.location.href = event.link;
						}}
					>
						{columns.map((column) => (
							<Fragment key={column}>{renderCell(event, column)}</Fragment>
						))}
					</tr>
				))}
			</tbody>
		</table>
	);
}
